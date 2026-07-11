#!/bin/bash
set -e

# ============================================
# Dinofy Master — Deploy Script
# Uso: bash deploy.sh [setup|update|status|logs|backup]
# ============================================

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
COMPOSE_FILE="$SCRIPT_DIR/docker-compose.prod.yml"
ENV_FILE="$SCRIPT_DIR/.env"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

log() { echo -e "${GREEN}[DINOFY]${NC} $1"; }
warn() { echo -e "${YELLOW}[AVISO]${NC} $1"; }
err() { echo -e "${RED}[ERRO]${NC} $1"; exit 1; }
ask() { read -p "$(echo -e "${CYAN}$1${NC}")" "$2"; }
ask_secret() { read -sp "$(echo -e "${CYAN}$1${NC}")" "$2"; echo; }

check_deps() {
    command -v docker >/dev/null 2>&1 || err "Docker nao instalado. Execute: curl -fsSL https://get.docker.com | sh"
    docker compose version >/dev/null 2>&1 || err "Docker Compose v2 nao encontrado."
    docker info >/dev/null 2>&1 || err "Docker daemon nao esta rodando ou usuario sem permissao. Execute: sudo usermod -aG docker \$USER"
}

generate_password() {
    openssl rand -base64 24 2>/dev/null | tr -d '/+=' | head -c 32
}

generate_app_key() {
    docker run --rm php:8.2-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;" 2>/dev/null
}

setup() {
    echo ""
    echo -e "${BOLD}╔══════════════════════════════════════════╗${NC}"
    echo -e "${BOLD}║      DINOFY MASTER — SETUP INICIAL       ║${NC}"
    echo -e "${BOLD}╚══════════════════════════════════════════╝${NC}"
    echo ""

    check_deps

    if [ -f "$ENV_FILE" ]; then
        echo -e "${YELLOW}Ja existe um .env configurado.${NC}"
        read -p "Deseja reconfigurar? (s/N): " RECONF
        if [ "$RECONF" != "s" ] && [ "$RECONF" != "S" ]; then
            log "Usando .env existente. Executando update..."
            update
            return
        fi
    fi

    # ─── Dominio ───
    echo ""
    echo -e "${BOLD}1. DOMINIO${NC}"
    echo -e "   Informe o dominio principal (ex: dinofy.cloud, meusite.com.br)"
    echo -e "   O sistema criara: admin.DOMINIO e master.DOMINIO"
    echo ""
    ask "   Dominio: " BASE_DOMAIN
    [ -z "$BASE_DOMAIN" ] && err "Dominio obrigatorio."
    BASE_DOMAIN=$(echo "$BASE_DOMAIN" | sed 's|https\?://||' | sed 's|/.*||')

    echo ""
    echo -e "   ${GREEN}admin.${BASE_DOMAIN}${NC}  → Painel administrativo"
    echo -e "   ${GREEN}master.${BASE_DOMAIN}${NC} → Portal do cliente / Checkout"
    echo ""

    # ─── Cloudflare ───
    echo -e "${BOLD}2. CLOUDFLARE (SSL Wildcard)${NC}"
    echo -e "   Necessario para certificado HTTPS automatico."
    echo -e "   Crie um API Token em: ${CYAN}dash.cloudflare.com → Profile → API Tokens${NC}"
    echo -e "   Permissao: Zone > DNS > Edit"
    echo ""
    ask "   Email Cloudflare: " CF_EMAIL
    ask_secret "   API Token DNS: " CF_TOKEN
    [ -z "$CF_EMAIL" ] && err "Email Cloudflare obrigatorio."
    [ -z "$CF_TOKEN" ] && err "Token Cloudflare obrigatorio."

    # ─── Admin ───
    echo ""
    echo -e "${BOLD}3. ADMIN DO PAINEL${NC}"
    ask "   Email do admin [${CF_EMAIL}]: " ADMIN_EMAIL
    ADMIN_EMAIL="${ADMIN_EMAIL:-$CF_EMAIL}"
    ask_secret "   Senha do admin: " ADMIN_PASSWORD
    [ -z "$ADMIN_PASSWORD" ] && err "Senha do admin obrigatoria."

    # ─── Senhas automaticas ───
    echo ""
    echo -e "${BOLD}4. GERANDO CREDENCIAIS...${NC}"

    DB_PASSWORD=$(generate_password)
    DB_ROOT_PASSWORD=$(generate_password)
    log "Senha MySQL gerada automaticamente."

    APP_KEY=$(generate_app_key)
    if [ -z "$APP_KEY" ]; then
        err "Falha ao gerar APP_KEY. Verifique se o Docker funciona: docker run --rm php:8.2-cli php -v"
    fi
    log "APP_KEY gerada: ${APP_KEY:0:20}..."

    # Traefik auth
    TRAEFIK_USER="admin"
    TRAEFIK_PASS=$(generate_password | head -c 16)
    if command -v htpasswd >/dev/null 2>&1; then
        TRAEFIK_AUTH=$(htpasswd -nbB "$TRAEFIK_USER" "$TRAEFIK_PASS" | sed 's/\$/\$\$/g')
    else
        TRAEFIK_AUTH=$(docker run --rm httpd:alpine htpasswd -nbB "$TRAEFIK_USER" "$TRAEFIK_PASS" 2>/dev/null | sed 's/\$/\$\$/g')
    fi
    log "Traefik dashboard: ${TRAEFIK_USER} / ${TRAEFIK_PASS}"

    # ─── Gerar .env ───
    echo ""
    log "Gravando .env..."

    cat > "$ENV_FILE" <<ENVEOF
# Dinofy Master — Gerado em $(date '+%Y-%m-%d %H:%M:%S')
# Dominio: ${BASE_DOMAIN}

APP_NAME="Dinofy Master"
APP_ENV=production
APP_DEBUG=false
APP_KEY=${APP_KEY}
APP_URL=https://admin.${BASE_DOMAIN}

DB_CONNECTION=mysql
DB_HOST=master-mysql
DB_PORT=3306
DB_DATABASE=dinofy_master
DB_USERNAME=master
DB_PASSWORD=${DB_PASSWORD}
MASTER_DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}

SESSION_DRIVER=database
CACHE_STORE=file
LOG_CHANNEL=stderr

BASE_DOMAIN=${BASE_DOMAIN}
DINOFY_IMAGE=dinofy_app:latest
TENANT_DATA_PATH=/srv/tenants

ACME_EMAIL=${CF_EMAIL}
CF_API_EMAIL=${CF_EMAIL}
CF_DNS_API_TOKEN=${CF_TOKEN}

TRAEFIK_AUTH=${TRAEFIK_AUTH}

ADMIN_EMAIL=${ADMIN_EMAIL}
ADMIN_PASSWORD=${ADMIN_PASSWORD}
ENVEOF

    chmod 600 "$ENV_FILE"
    log ".env criado com permissoes restritas (600)."

    # ─── Diretorios ───
    log "Criando diretorios..."
    sudo mkdir -p /srv/tenants
    sudo chown -R "$(id -u):$(id -g)" /srv/tenants

    # ─── Rede Docker ───
    log "Criando rede traefik-public..."
    docker network create traefik-public 2>/dev/null || true

    # ─── Build e Start ───
    log "Construindo e iniciando containers..."
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d --build

    log "Aguardando servicos..."
    sleep 15

    # ─── Status ───
    echo ""
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps
    echo ""

    echo -e "${BOLD}╔══════════════════════════════════════════╗${NC}"
    echo -e "${BOLD}║         DEPLOY CONCLUIDO!                ║${NC}"
    echo -e "${BOLD}╚══════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "  ${BOLD}URLs:${NC}"
    echo -e "  Admin:     ${GREEN}https://admin.${BASE_DOMAIN}${NC}"
    echo -e "  Cliente:   ${GREEN}https://master.${BASE_DOMAIN}${NC}"
    echo -e "  Traefik:   ${GREEN}https://traefik.${BASE_DOMAIN}${NC}"
    echo ""
    echo -e "  ${BOLD}Credenciais admin:${NC}"
    echo -e "  Email:     ${CYAN}${ADMIN_EMAIL}${NC}"
    echo -e "  Senha:     (a que voce definiu)"
    echo ""
    echo -e "  ${BOLD}Traefik dashboard:${NC}"
    echo -e "  Usuario:   ${CYAN}${TRAEFIK_USER}${NC}"
    echo -e "  Senha:     ${CYAN}${TRAEFIK_PASS}${NC}"
    echo ""
    echo -e "  ${YELLOW}IMPORTANTE — Configure o DNS no Cloudflare:${NC}"
    echo -e "  ${BASE_DOMAIN}     →  A  →  $(curl -s ifconfig.me 2>/dev/null || echo 'IP_DA_VPS')  (Proxy ON)"
    echo -e "  *.${BASE_DOMAIN}   →  A  →  $(curl -s ifconfig.me 2>/dev/null || echo 'IP_DA_VPS')  (Proxy ON)"
    echo ""
    echo -e "  ${YELLOW}Salve essas credenciais! A senha do Traefik nao sera mostrada novamente.${NC}"
    echo ""
}

update() {
    log "=== ATUALIZANDO ==="
    check_deps
    [ ! -f "$ENV_FILE" ] && err ".env nao encontrado. Execute: bash deploy.sh setup"

    log "Rebuild e restart..."
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d --build

    log "Limpando imagens antigas..."
    docker image prune -f 2>/dev/null || true

    echo ""
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps
    log "=== UPDATE CONCLUIDO ==="
}

status() {
    log "=== STATUS ==="
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps 2>/dev/null || docker compose -f "$COMPOSE_FILE" ps
    echo ""
    log "Containers ativos:"
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | head -30
    echo ""
    log "Uso de disco:"
    docker system df
}

show_logs() {
    local service="${1:-master}"
    log "Logs de: $service (Ctrl+C para sair)"
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" logs -f --tail=100 "$service"
}

backup() {
    log "=== BACKUP ==="
    [ ! -f "$ENV_FILE" ] && err ".env nao encontrado."
    source "$ENV_FILE"

    local BACKUP_DIR="/srv/backups/$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"

    log "Dump do MySQL..."
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" exec -T master-mysql \
        mysqldump -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" \
        --single-transaction --routines --triggers \
        > "$BACKUP_DIR/dinofy_master.sql"

    log "Backup storage..."
    docker cp "$(docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps -q master):/var/www/html/storage" "$BACKUP_DIR/storage" 2>/dev/null || true

    log "Backup .env..."
    cp "$ENV_FILE" "$BACKUP_DIR/.env.bak"

    local SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)
    log "Backup salvo em: $BACKUP_DIR ($SIZE)"
}

domain() {
    [ ! -f "$ENV_FILE" ] && err ".env nao encontrado."
    source "$ENV_FILE"
    echo ""
    echo -e "  Dominio:   ${GREEN}${BASE_DOMAIN}${NC}"
    echo -e "  Admin:     ${GREEN}https://admin.${BASE_DOMAIN}${NC}"
    echo -e "  Cliente:   ${GREEN}https://master.${BASE_DOMAIN}${NC}"
    echo -e "  Traefik:   ${GREEN}https://traefik.${BASE_DOMAIN}${NC}"
    echo ""
}

# ============================================
case "${1:-}" in
    setup)  setup ;;
    update) update ;;
    status) status ;;
    logs)   show_logs "$2" ;;
    backup) backup ;;
    domain) domain ;;
    *)
        echo ""
        echo -e "${BOLD}Dinofy Master — Deploy${NC}"
        echo ""
        echo "  bash deploy.sh setup    Primeiro deploy (pergunta dominio, gera .env)"
        echo "  bash deploy.sh update   Atualiza codigo e rebuild"
        echo "  bash deploy.sh status   Status dos containers"
        echo "  bash deploy.sh logs     Logs (ex: deploy.sh logs master)"
        echo "  bash deploy.sh backup   Backup do banco e storage"
        echo "  bash deploy.sh domain   Mostra dominio configurado"
        echo ""
        ;;
esac
