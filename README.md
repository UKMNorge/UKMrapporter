# UKMrapporter

### Hvordan oppretter man en ny rapport?
Oppretter overnatting rapport eksempel
1. Opprett en fil på `UKMrapporter/rapporter/Overnatting.php` som utvider Rapport (UKMrapporter/class/Framework/Rapport.php)
2. Opprett en mappe på `/UKMrapporter/twig/Overnatting`
3. I `/UKMrapporter/twig/Overnatting` opprett 2 filer (rapport.html.twig og tilpass.html.twig)
4. `rapport.html.twig` fra punkt 3 skal ha informasjon om selve rapporten og hvordan det skal se ut
5. `tilpass.html.twig` fra punkt 3 skal innholde filtere og opsjoner for rapporten
6. I tilleg kan man legge til nye kategorier på UKMrapporter/rapporter/kategorier.yml

[![Visualisering av ny rapport](https://github.com/UKMNorge/UKMrapporter/blob/master/docs/img/Rapporter%20-%20Ny%20rapport.png?raw=true)
