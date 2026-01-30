# 🛰️ Flugradar (Flight Radar)

[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg?style=flat-square)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-8.1-blue.svg?style=flat-square)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-1.0.20260110-orange.svg?style=flat-square)](https://github.com/Wilkware/FlightRadar)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg?style=flat-square)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Actions](https://img.shields.io/github/actions/workflow/status/wilkware/LocalTuya/ci.yml?branch=main&label=CI&style=flat-square)](https://github.com/Wilkware/FlightRadar/actions)

Das Modul empfängt Flugdaten über MQTT vom Service [flights2mqtt](https://github.com/Wilkware/flights2mqtt) und stellt sie in der TileVisu übersichtlich dar.

## Inhaltverzeichnis

1. [Funktionsumfang](#user-content-1-funktionsumfang)
2. [Voraussetzungen](#user-content-2-voraussetzungen)
3. [Installation](#user-content-3-installation)
4. [Einrichten der Instanzen in IP-Symcon](#user-content-4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#user-content-5-statusvariablen-und-profile)
6. [Visualisierung](#user-content-6-visualisierung)
7. [PHP-Befehlsreferenz](#user-content-7-php-befehlsreferenz)
8. [Versionshistorie](#user-content-8-versionshistorie)

### 1. Funktionsumfang

Mit diesem Modul können Flugdaten aus dem MQTT-Service [flights2mqtt](https://github.com/Wilkware/flights2mqtt) direkt in der TileVisu angezeigt werden.  
Nach der Installation und Konfiguration des Services kann das Modul das gewünschte MQTT-Topic abonnieren und die aktuellen Flugbewegungen übersichtlich darstellen.  
Zusätzlich lässt sich die Haltezeit der Flugdaten im Modul einstellen, sodass die Anzeige immer aktuell bleibt, ohne dass ältere Daten sofort verschwinden.

### 2. Voraussetzungen

* IP-Symcon ab Version 8.1

Notwendige Voraussetzung ist eine funktionsfähige und laufende Installation von [flights2mqtt](https://github.com/Wilkware/flights2mqtt). Dessen Installation, Konfiguration und der Betrieb ist hier beschrieben: [README](https://github.com/Wilkware/flights2mqtt/blob/main/README.md).

### 3. Installation

* Über den Modul Store die Bibliothek _Flugradar_ installieren.
* Alternativ Über das Modul-Control folgende URL hinzufügen.  
`https://github.com/Wilkware/FlightRadar` oder `git://github.com/Wilkware/FlightRadar.git`

### 4. Einrichten der Instanzen in IP-Symcon

* Unter "Instanz hinzufügen" ist das _'Flugradar'_-Modul unter dem Hersteller _'(Geräte)'_ aufgeführt.

__Konfigurationsseite__:

Einstellungsbereich:

> ✈️ Flugdaten ...

Name                        | Beschreibung
--------------------------- | ----------------------------------
MQTT Base Topic             | Ist das grundlegende Themenpräfix, unter dem alle spezifischen Subtopics für Nachrichten in einem MQTT-System organisiert werden. Standardmäßig ist der Präfix auf _'flights'_ vorbelegt.
MQTT Topic                  | Ist der eindeutige Pfad, der zum Veröffentlichen und Abonnieren von Nachrichten verwendet wird. __HINWEIS:__ Immer in Kleinbuchstaben angeben!

> ⏱️ Zeitsteuerung ...

Name                        | Beschreibung
--------------------------- | ----------------------------------
Ablaufzeit                  | Legt die Haltezeit der Flugdaten in Minuten fest. Die Flüge werden so lange angezeigt, auch wenn sie nicht mehr im definierten Flugbereich auftreten.

### 5. Statusvariablen und Profile

Es werden keine zusätzlichen Statusvariablen/Profile benötigt.

### 6. Visualisierung

Das Modul kann direkt oder als Link in die TileVisu eingehangen werden.  
Jeder Flug wird pro Kachel angezeigt und es kann durch die einzelnene Flüge durchnavigieren.

### 7. PHP-Befehlsreferenz

Das Modul stellt keine direkten Funktionsaufrufe zur Verfügung.

### 8. Versionshistorie

v1.0.20260110

* _NEU_: Initialversion

## Entwickler

Seit nunmehr über 10 Jahren fasziniert mich das Thema Haussteuerung. In den letzten Jahren betätige ich mich auch intensiv in der IP-Symcon Community und steuere dort verschiedenste Skript und Module bei. Ihr findet mich dort unter dem Namen @pitti ;-)

[![GitHub](https://img.shields.io/badge/GitHub-@wilkware-181717.svg?style=for-the-badge&logo=github)](https://wilkware.github.io/)

## Spenden

Die Software ist für die nicht kommerzielle Nutzung kostenlos, über eine Spende bei Gefallen des Moduls würde ich mich freuen.

[![PayPal](https://img.shields.io/badge/PayPal-spenden-00457C.svg?style=for-the-badge&logo=paypal)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166)

## Lizenz

Namensnennung - Nicht-kommerziell - Weitergabe unter gleichen Bedingungen 4.0 International

[![Licence](https://img.shields.io/badge/License-CC_BY--NC--SA_4.0-EF9421.svg?style=for-the-badge&logo=creativecommons)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
