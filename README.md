# Translatable for Moodle

Translatable is a derivative of [Content Translation Manager](https://moodle.org/plugins/tool_translationmanager) and [Fulltranslate](https://moodle.org/plugins/filter_fulltranslate) merged into a single plugin. It provides a content translation page for courses and automatic machine translation using the DeepL Pro Translation api. This filter needs to be placed first in your filter stack.

## Installation

Clone or download this plugin to ```/moodlewww/filter/translatable``` and run through the Database upgrade process. This plugin adds a ```filter_translatable``` table to your database to store and query translated strings in your courses.

## Configuration

```Navigate to Site Administration -> Plugins -> Filter -> Translatable Content.```

This plugin provides integration with the [DeepL Pro API.](https://www.deepl.com/en/docs-api/) In order to use this integration, you must signup for a Pro account and generate an API key. You can then save the API key to the Translatable Content settings page.

There are two options for autogenerating machine translations. You can do it **ondemand** with page load. This can cause long initial page loads and cause your server to time out if your php memory is not high enough.

The other option is to enable autotranslation on the Translate Content page and click the Autotranslate button. This will generate and automatically save translations on the Translate Content page.

## Generating a translation

The first step is to visit a course and then change to your translation language using the Moodle locale switcher. This generates the needed table records for translation. You also need to go into each activity in order to generate table records for content in the activity as well. After you have visited each part of your course in the language you want a translation for, you can click on the course action menu and navigate to Translate Content.

From this page, you can autotranslate content if you do not have ondemand autotranslation enabled. You can also hand translate string by string for your course.

<img src="https://ik.imagekit.io/yna8qytrq3i/tcm/action-menu_dNXPhjsxR.png" alt="Course Action Menu" />

<img src="https://ik.imagekit.io/yna8qytrq3i/tcm/translate-content_BmAgFAtnH.png" alt="Translate Content" />

## Submit an issue

Please [submit issues here.](https://github.com/jamfire/moodle-filter_translatable/issues)

## Changelog

See the [CHANGES.md](CHANGES.md) documentation.md.

## Contributing

See the [CONTRIBUTING.md](CONTRIBUTING.md) documentation.