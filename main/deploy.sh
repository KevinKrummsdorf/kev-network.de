#!/bin/bash

SRC_DIR="/home/kevin/projects/kev-network.de/main"
DEST_DIR="/var/www/clients/client0/web5/web"

echo "🔁  Starte Deployment nach  $DEST_DIR"
echo "    Quelle: $SRC_DIR"

# Hauptverzeichnis ohne den 'public'-Ordner synchronisieren
rsync -av --delete \
  --exclude=".git" \
  --exclude="*.code-workspace" \
  --exclude="deploy.sh" \
  --exclude="public/" \
  --exclude=".env/" \
  "$SRC_DIR/" "$DEST_DIR/"

# Inhalt von 'public/' in das Ziel-Rootverzeichnis kopieren
rsync -av "$SRC_DIR/public/" "$DEST_DIR/"

echo "✅  Deployment abgeschlossen."
