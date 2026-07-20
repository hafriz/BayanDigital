#!/bin/bash
###############################################################################
# deploy.sh – Deploy BayanDigital Masjid Smart Screen to Kubernetes
###############################################################################
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
NS="bayandigital"
HARBOR="harbor.development.rarecreation.xyz"
IMAGE="${HARBOR}/bayandigital/app:latest"
K8S_DIR="${SCRIPT_DIR}/k8s"

echo ""
echo "════════════════════════════════════════════════════════════"
echo "  Deploying BayanDigital Masjid Smart Screen"
echo "  URL: https://bayandigital.rarecreation.xyz"
echo "════════════════════════════════════════════════════════════"
echo ""

# ── Create namespace if not exists ────────────────────────────────────────────
echo "==> Creating namespace..."
kubectl apply -f "${K8S_DIR}/00-namespace.yaml"

# ── Create harbor pull secret ────────────────────────────────────────────────
echo "==> Creating Harbor imagePullSecret..."
kubectl get secret harbor-registry-creds -n "${NS}" &>/dev/null || \
    kubectl create secret docker-registry harbor-registry-creds \
        -n "${NS}" \
        --docker-server="${HARBOR}" \
        --docker-username=hafriz \
        --docker-password='Sc278resor!' \
        --dry-run=client -o yaml | kubectl apply -f -

# ── Apply secrets ────────────────────────────────────────────────────────────
echo "==> Applying secrets..."
kubectl apply -f "${K8S_DIR}/01-secrets.yaml"

# ── Generate APP_KEY if placeholder ──────────────────────────────────────────
APP_KEY=$(kubectl get secret -n "${NS}" bayandigital-secrets -o jsonpath='{.data.APP_KEY}' 2>/dev/null | base64 -d 2>/dev/null || echo "")
if [[ -z "$APP_KEY" || "$APP_KEY" == *"generated"* ]]; then
    NEW_KEY=$(head -c 32 /dev/urandom | base64)
    echo "==> Generating new APP_KEY..."
    kubectl patch secret bayandigital-secrets -n "${NS}" -p="{\"data\":{\"APP_KEY\":\"$(echo -n "base64:${NEW_KEY}" | base64)\"}}"
fi

# ── Apply configmap ──────────────────────────────────────────────────────────
echo "==> Applying configmap..."
kubectl apply -f "${K8S_DIR}/02-configmap.yaml"

# ── Apply MariaDB ────────────────────────────────────────────────────────────
echo "==> Applying MariaDB..."
kubectl apply -f "${K8S_DIR}/03-mariadb-pvc.yaml"
kubectl apply -f "${K8S_DIR}/04-mariadb.yaml"

echo "==> Waiting for MariaDB..."
kubectl wait --for=condition=ready pod -l app.kubernetes.io/name=mariadb \
    -n "${NS}" --timeout=120s 2>/dev/null || true

# ── Apply Redis ──────────────────────────────────────────────────────────────
echo "==> Applying Redis..."
kubectl apply -f "${K8S_DIR}/05-redis.yaml"

echo "==> Waiting for Redis..."
kubectl wait --for=condition=ready pod -l app.kubernetes.io/name=redis \
    -n "${NS}" --timeout=60s 2>/dev/null || true

# ── Apply App ────────────────────────────────────────────────────────────────
echo "==> Applying application..."
kubectl apply -f "${K8S_DIR}/06-app.yaml"

# ── Apply Ingress ────────────────────────────────────────────────────────────
echo "==> Applying ingress..."
kubectl apply -f "${K8S_DIR}/07-ingress.yaml"

# ── Wait for app ─────────────────────────────────────────────────────────────
echo "==> Waiting for BayanDigital app (timeout 180s)..."
kubectl wait --for=condition=ready pod -l app.kubernetes.io/name=bayandigital \
    -n "${NS}" --timeout=180s 2>/dev/null || true

echo ""
echo "════════════════════════════════════════════════════════════"
echo "  Deployment complete!"
echo ""
echo "  URL:      https://bayandigital.rarecreation.xyz"
echo "  Namespace: bayandigital"
echo ""
echo "  Pods:"
kubectl get pods -n "${NS}" -o wide
echo ""
echo "  Services:"
kubectl get svc -n "${NS}"
echo ""
echo "  Ingress:"
kubectl get ingress -n "${NS}"
echo "════════════════════════════════════════════════════════════"
