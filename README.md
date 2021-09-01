# Gravity Scores

Gravity Scores is a WordPress Plugin. It visualizes user results provided by Tests that are implemented using Gravity Forms.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Development](#development)
- [Support](#support)
- [Contributing](#contributing)
- [Examples](#examples)

## Installation

Clone this repository and execute `build.sh` to build an installable zip-file.

```sh
git clone git@gitlab.rlp.net:jdillenberger/gravityscores.git
cd ./gravityscores
bash ./build.sh
```

You can now upload the installable zip-archive in WordPress admin to install it and then activate it. [[More Info the about WordPress Plugin installation]](https://wordpress.org/support/article/managing-plugins/#manual-upload-via-wordpress-admin)

> Make sure [Gravity Forms](https://www.gravityforms.com/) is installed when you install Gravity Scores.

## Usage

After Gravity Scores is installed and activated in WordPress the admin menu contains a `Scores` section
which then includes the following subsections:

- All Evaluations
- Add Evaluation
- All Tests
- Add Test
- Import/Export
- View Log

If we already have a Gravity Forms `form`, we can build a test from that form using the `Add Test` wizzard. 
After that we can use the `Add Evaluation` wizzard to build an Evaluation - which connects multiple subscales from existing tests with one visualization. After that a shortcode for this evaluation can be copied from the `All Evaluations` section.
Use this shortcode in a Page or Article to show the visualized results for the user.

Example evaluations are shown on the bottom of this document. 

## Development

### Test Environment

For Development a vagrant-box is used. Make sure [vagrant](https://www.vagrantup.com/) and [npm](https://www.npmjs.com/) are installed.

```sh
git clone git@gitlab.rlp.net:jdillenberger/gravityscores.git
cd ./gravityscores
npm install
npm run dev
vagrant up
```

The `npm run dev` then needs to be executed if the JavaScript code has been modified.
If you want to "compile" the JavaScript for production use `npm run build` instead.

### PHP Development in General

Each plugin-feature is implemented is self-contained file, which is **included as a hook** in `gravityscores.php`.
The these php-components do not depend on each other - but in case of `rest-api` functions some JavaScript components depend on the api.
In the beginning of `gravityscores.php` are some dependency **includes**. Some hooks depend on these libraries.

All **hooks** can be found inside of the `hooks` directory. All library includes are inside the `inc` directory.

Many hooked features have a frontend-representation. The templates for these representations are in the `tpl` directory. 

### JavaScript Development in General

All JavaScript dependencies are installed via `npm install`.
Our JavaScript Implementation is fully contained inside of the `js` directory. 

All JavaScript files are in the `js` directory. They are compiled using the following commands:

- `npm run build`
- `npm run dev`

After the build is done, they are stored in `jsbuild`.
The **source-files** in the `js` directory are not directly used by the plugin.

If a new script endpoint needs to be added in the matching section to `webpack.config.js`.
To load a newly added script afterwards it needs to be enqueued using the [wp_enqueue_script](https://developer.wordpress.org/reference/functions/wp_enqueue_script/) function in php.

The `JavaScript` php-class adds some minor-functions for convenient development in JavaScript.
It is defined in `inc/javascript.php` and provides funktion you can apply for the slug of any loaded JavaScript.

All the libraries used are listed as dependencies in `package.json`.

### CSS Development in General

Each section or chart has its own **css-file** in `css`.
The files don't have any sideeffects for sections, for which they are not defined.

To load a new css-file to a section use the [wp_enqueue_style](https://developer.wordpress.org/reference/functions/wp_enqueue_style/) function in php.

### Special Usecases

- A guide on how to **implement a new visualisation** type will be added in the future.
- A guide on how to **implement support for a new type of question** will be added in the future.

## Support

Please [open an issue](https://gitlab.rlp.net/jdillenberger/gravityscores/-/issues/new) for support.

## Contributing

Please contribute using [Github Flow](https://guides.github.com/introduction/flow/). Create a branch, add commits, and [open a merge request](https://gitlab.rlp.net/jdillenberger/gravityscores/-/merge_requests/new).


## Examples

An example evaluation build using the Gravity Scores radar chart could look as follows:

![Example](https://jandillenberger.com/projects/PASST/gravityscores_radarchart.png)
