#!/bin/bash
set -e

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

install_deps() {
    export DEBIAN_FRONTEND=noninteractive

    if ! command -v curl >/dev/null 2>&1 || ! command -v git >/dev/null 2>&1 || ! command -v openssl >/dev/null 2>&1; then
        log "Instalando dependencias basicas..."
        apt-get update -qq >/dev/null 2>&1 || true
        apt-get install -y -qq curl ca-certificates gnupg git openssl >/dev/null 2>&1 || true
    fi
}

install_docker() {
    log "Instalando Docker (pode levar alguns minutos)..."
    export DEBIAN_FRONTEND=noninteractive

    apt-get update -qq >/dev/null 2>&1
    apt-get install -y -qq ca-certificates curl gnupg >/dev/null 2>&1

    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
    chmod a+r /etc/apt/keyrings/docker.asc

    . /etc/os-release 2>/dev/null || true
    CODENAME="${VERSION_CODENAME:-focal}"
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $CODENAME stable" > /etc/apt/sources.list.d/docker.list

    apt-get update -qq >/dev/null 2>&1
    apt-get install -y -qq docker-ce docker-ce-cli containerd.io docker-compose-plugin docker-buildx-plugin >/dev/null 2>&1

    systemctl enable docker >/dev/null 2>&1 || true
    systemctl start docker >/dev/null 2>&1 || true

    docker info >/dev/null 2>&1 || err "Docker nao iniciou."
    log "Docker instalado!"
}

check_deps() {
    install_deps
    if ! command -v docker >/dev/null 2>&1; then
        install_docker
    fi
    docker info >/dev/null 2>&1 || err "Docker nao esta rodando."
    docker compose version >/dev/null 2>&1 || err "Docker Compose v2 nao encontrado."
}

generate_password() {
    openssl rand -base64 24 2>/dev/null | tr -d '/+=' | head -c 24
}

setup() {
    echo ""
    echo -e "${BOLD}╔══════════════════════════════════════════╗${NC}"
    echo -e "${BOLD}║      DINOFY MASTER — SETUP INICIAL       ║${NC}"
    echo -e "${BOLD}╚══════════════════════════════════════════╝${NC}"
    echo ""

    check_deps

    if [ -f "$ENV_FILE" ]; then
        warn "Ja existe um .env. Deseja reconfigurar? (s/N)"
        read -r RECONF
        if [ "$RECONF" != "s" ] && [ "$RECONF" != "S" ]; then
            update
            return
        fi
    fi

    echo -e "${BOLD}1. DOMINIO${NC}"
    echo "   Informe o dominio apontado para esta VPS."
    echo "   Admin: DOMINIO/admin  |  Cliente: DOMINIO/client"
    echo ""
    ask "   Dominio (ex: pay.srvbr.top): " APP_DOMAIN
    [ -z "$APP_DOMAIN" ] && err "Dominio obrigatorio."
    APP_DOMAIN=$(echo "$APP_DOMAIN" | sed 's|https\?://||' | sed 's|/.*||')

    if lsof -i :80 >/dev/null 2>&1 || ss -tlnp | grep -q ':80 ' 2>/dev/null; then
        warn "Porta 80 em uso. Parando servicos conflitantes..."
        systemctl stop apache2 2>/dev/null || true
        systemctl disable apache2 2>/dev/null || true
        systemctl stop nginx 2>/dev/null || true
        systemctl disable nginx 2>/dev/null || true
        sleep 1
        if lsof -i :80 >/dev/null 2>&1 || ss -tlnp | grep -q ':80 ' 2>/dev/null; then
            err "Porta 80 ainda em uso. Libere a porta e tente novamente."
        fi
        log "Porta 80 liberada."
    fi

    echo ""
    echo -e "${BOLD}2. ADMIN${NC}"
    ask "   Email do admin: " ADMIN_EMAIL
    [ -z "$ADMIN_EMAIL" ] && err "Email obrigatorio."
    ask_secret "   Senha do admin: " ADMIN_PASSWORD
    [ -z "$ADMIN_PASSWORD" ] && err "Senha obrigatoria."

    echo ""
    echo -e "${BOLD}3. GERANDO CREDENCIAIS...${NC}"
    DB_PASSWORD=$(generate_password)
    DB_ROOT_PASSWORD=$(generate_password)
    log "Senhas MySQL geradas."

    APP_KEY=$(docker run --rm php:8.2-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;" 2>/dev/null)
    [ -z "$APP_KEY" ] && err "Falha ao gerar APP_KEY."
    log "APP_KEY gerada."

    log "Gravando .env..."
    cat > "$ENV_FILE" <<ENVEOF
APP_NAME="Dinofy Master"
APP_ENV=production
APP_DEBUG=false
APP_KEY=${APP_KEY}
APP_URL=https://${APP_DOMAIN}
APP_DOMAIN=${APP_DOMAIN}

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

BASE_DOMAIN=${APP_DOMAIN}
DINOFY_IMAGE=dinofy_app:latest
TENANT_DATA_PATH=/srv/tenants

ADMIN_EMAIL=${ADMIN_EMAIL}
ADMIN_PASSWORD=${ADMIN_PASSWORD}
ENVEOF

    chmod 600 "$ENV_FILE"
    log ".env criado."

    mkdir -p /srv/tenants
    docker network create traefik-public 2>/dev/null || true

    log "Construindo containers (primeira vez demora ~10min)..."
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d --build

    log "Aguardando servicos..."
    sleep 15

    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps
    echo ""
    echo -e "${BOLD}╔══════════════════════════════════════════╗${NC}"
    echo -e "${BOLD}║         DEPLOY CONCLUIDO!                ║${NC}"
    echo -e "${BOLD}╚══════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "  Admin:    ${GREEN}https://${APP_DOMAIN}/admin${NC}"
    echo -e "  Cliente:  ${GREEN}https://${APP_DOMAIN}/client${NC}"
    echo -e "  Checkout: ${GREEN}https://${APP_DOMAIN}/checkout${NC}"
    echo ""
    echo -e "  Login:    ${CYAN}${ADMIN_EMAIL}${NC}"
    echo ""
    echo -e "${YELLOW}CLOUDFLARE: Se o dominio usa Cloudflare, configure SSL/TLS para 'Flexible'.${NC}"
    echo ""
}

update() {
    [ ! -f "$ENV_FILE" ] && err ".env nao encontrado. Execute: bash deploy.sh setup"
    log "Atualizando..."
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d --build
    docker image prune -f >/dev/null 2>&1 || true
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps
    log "Update concluido!"
}

status() {
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps 2>/dev/null || docker ps
}

show_logs() {
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" logs -f --tail=100 "${1:-master}"
}

backup() {
    [ ! -f "$ENV_FILE" ] && err ".env nao encontrado."
    source "$ENV_FILE"
    local DIR="/srv/backups/$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$DIR"
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" exec -T master-mysql \
        mysqldump -u"${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" --single-transaction > "$DIR/db.sql"
    cp "$ENV_FILE" "$DIR/.env.bak"
    log "Backup em: $DIR ($(du -sh "$DIR" | cut -f1))"
}

case "${1:-}" in
    setup)  setup ;;
    update) update ;;
    status) status ;;
    logs)   show_logs "$2" ;;
    backup) backup ;;
    *)
        echo "Uso: bash deploy.sh [setup|update|status|logs|backup]"
        ;;
esac
