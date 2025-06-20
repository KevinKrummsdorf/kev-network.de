#!/bin/bash

SRC_DIR="/home/kevin/projects/kev-network.de/status"
DEST_DIR="/var/www/clients/client0/web18/web"
PRIVATE_DIR="/var/www/clients/client0/web18/private"

echo "🔁 Starte Deployment von Status-Projekt"
echo "    Quelle: $SRC_DIR"
echo "    Ziel (Web): $DEST_DIR"
echo "    Ziel (.env): $PRIVATE_DIR"

# Web-Dateien synchronisieren
rsync -av --delete \
  --exclude=".git" \
  --exclude="*.code-workspace" \
  --exclude="deploy.sh" \
  "$SRC_DIR/public/assets/" "$DEST_DIR/assets/"

rsync -av --delete "$SRC_DIR/public/css/" "$DEST_DIR/css/"
rsync -av --delete "$SRC_DIR/public/js/" "$DEST_DIR/js/"
rsync -av --delete "$SRC_DIR/templates/" "$DEST_DIR/templates/"
rsync -av --delete "$SRC_DIR/src/" "$DEST_DIR/src/"
rsync -av --delete "$SRC_DIR/routes/" "$DEST_DIR/routes/"
rsync -av "$SRC_DIR/index.php" "$DEST_DIR/index.php"
rsync -av "$SRC_DIR/.htaccess" "$DEST_DIR/.htaccess"
rsync -av "$SRC_DIR/vendor/" "$DEST_DIR/vendor/"


# .env gesondert behandeln (nicht ins Web-Verzeichnis legen!)
if [ -f "$SRC_DIR/.env" ]; then
  echo "🔐 Übertrage .env nach $PRIVATE_DIR"
  rsync -av "$SRC_DIR/.env" "$PRIVATE_DIR/.env"
fi

echo "✅ Deployment abgeschlossen."
