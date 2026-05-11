# PlotSquared & CMI Web Query

Ein einfaches Web-Interface, um Grundstücke von Spielern über die Datenbank von PlotSquared und CMI abzufragen.

## Features
* Suche per Spielername (Mojang API Integration) oder UUID.
* Zeigt alle Grundstücke an, auf denen der Spieler Owner, Trusted oder Helper ist.
* "Visit"-Befehl kann mit einem Klick kopiert werden.
* Clean Dark-Design mit Bootstrap 5.

## Voraussetzungen
* PHP 7.4 oder höher
* PDO MySQL Erweiterung
* Zugriff auf die Datenbanken von **PlotSquared** und **CMI**.
* Funktioniert nur wenn die beide Datenbanken auf dem gleichen Server sind.

## Installation
1. Lade die `plot.php` auf deinen Webserver hoch.
2. Öffne die Datei und trage deine Datenbank-Zugangsdaten im `$config` Array ein.
3. Achte darauf, dass dein Webserver Zugriff auf die MySQL-Ports des Minecraft-Servers hat (Whitelist).

## Lizenz
Dieses Projekt ist unter der [GNU GPLv3](LICENSE) lizenziert.
