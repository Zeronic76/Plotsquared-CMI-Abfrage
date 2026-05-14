# PlotSquared & CMI || Luckperms Web Query

Ein einfaches Web-Interface, um Grundstücke von Spielern über die Datenbank von PlotSquared und CMI abzufragen.

## Features
* Suche per Spielername (Mojang API Integration) oder UUID.
* Zeigt alle Grundstücke an, auf denen der Spieler Owner, Trusted oder Helper ist.
* "Visit"-Befehl kann mit einem Klick kopiert werden.
* Clean Dark-Design mit Bootstrap 5.

## Voraussetzungen
* PHP 7.4 oder höher
* PDO MySQL Erweiterung
* Zugriff auf die Datenbanken von **PlotSquared** und **CMI** oder **Luckperms**.
* Funktioniert nur wenn die Datenbanken auf dem gleichen Server sind.

## Installation

Dank des neuen Installers ist die Einrichtung in Sekunden erledigt:

1. Lade alle Dateien (inklusive der `install.php`) auf deinen Webserver hoch.
2. Rufe die `install.php` in deinem Browser auf (z. B. `deinedomain.de/install.php`).
3. Folge den Anweisungen im Installer, um deine Datenbank-Daten und Sprache einzustellen.
4. **Wichtig:** Lösche die `install.php` nach der erfolgreichen Installation vom Server!

Das Skript erstellt deine `config.inc.php` automatisch für dich.
## Lizenz
Dieses Projekt ist unter der [GNU GPLv3](LICENSE) lizenziert.
