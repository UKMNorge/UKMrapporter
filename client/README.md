# client-blueprint-vue3

Vue3, Vite og Typescript oppsetting for nye prosjekter på klient side

## Hva brukes repoet for?
Repoet brukes for å lage klient side generelt og er tilpasset for bruk internt i WP plugins.

## Kom i gang

```bash
# Installer alle pakker fra NPM (node_modules)
$ npm i

#  Build koden
$ npm run build

# Skal du jobbe med koden, da kan du watche endringer (OBS: fungerer ikke fra Vagrant. Kjør det gjerne fra maskina di): 
$ npm run watch
```


## Hvordan kan man bruke client-blueprint-vue3 i WP plugin
1. Klone repoet
```bash
$ git clone https://github.com/UKMNorge/client-blueprint-vue3 client
```

2. Lage pekere til JS og CSS
```php
// I hovedfilen av WP plugin

wp_enqueue_style('UKMstatistikkVueStyle', plugin_dir_url(__FILE__) . '/client/dist/assets/build.css');
wp_enqueue_script('UKMstatistikkVueJs', plugin_dir_url(__FILE__) . '/client/dist/assets/build.js','','',true);
```

VIKTIG!
3. Fjern .git mappe og .gitignore fil slik at repo fra WP plugin kan ta med endringer i client mappe
```bach
# Naviger til client
$ cd client

# Slett .git mappe og .gitignore fil
$ rm -r .git .gitignore
```

4. Legg til koden på html kode på hoved twig eller html fil

```html
<div id="app">

</div>
```


### src_example
Bruk src_example mappe for å komme i gang og for å se hvordan kompontenter kan importeres
