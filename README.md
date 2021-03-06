# mediawiki-nagvis

This is an extension for viewing [**NagVis**](http://nagvis.org) maps in Mediawiki

Documentation can be found at [Mediawiki (Extension:Nagvis)](http://www.mediawiki.org/wiki/Extension:NagVis)

[See a demo of NagVis extension](http://www.delegacionprovincial.com/mediawiki/index.php/Gestion_Online:IcingaNagvis16)

# Installation

- Download extension, extract files to a folder and rename folder as `NagVis`.
- Upload `NagVis` folder to your extensions directory from the root of your MediaWiki installation.
- Add `require_once "$IP/extensions/NagVis/NagVis.php";` to your `LocalSettings.php` file (near the end)

(More details about installation at [Extension:NagVis#installation](https://www.mediawiki.org/wiki/Extension:NagVis#Installation))

# Screenshot (Mediawiki + NagVis extension)
![](https://upload.wikimedia.org/wikipedia/mediawiki/1/14/NagVis_Header.png)

# Credits
This extension use the following software:
- PHP Simple HTML DOM Parser at http://simplehtmldom.sourceforge.net/

and works with:
- NagVis http://nagvis.org
- Icinga https://www.icinga.org/
- Nagios https://www.nagios.org/
