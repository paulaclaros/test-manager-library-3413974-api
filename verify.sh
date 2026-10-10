#!/usr/bin/env bash
set -e

echo "=== Verificación de Manager Library API ==="
echo "1. Ejecutando pruebas unitarias de Dominio y Arquitectura..."
./vendor/bin/phpunit --testdox

echo "=== Todas las verificaciones pasaron con éxito ==="
