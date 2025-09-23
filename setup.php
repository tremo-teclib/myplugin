<?php


/**
 * -------------------------------------------------------------------------
 * myplugin plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2025 by the myplugin plugin team.
 * @license   MIT https://opensource.org/licenses/mit-license.php
 * @link      https://github.com/pluginsGLPI/myplugin
 * -------------------------------------------------------------------------
 */

use GlpiPlugin\Myplugin\Superasset;
use GlpiPlugin\Myplugin\Superasset_Item;
use GlpiPlugin\Myplugin\Config;
use GlpiPlugin\Myplugin\Profile as Myplugin_profile;
use Glpi\Plugin\Hooks;
use GlpiPlugin\Myplugin\Dashboard;

define('PLUGIN_MYPLUGIN_VERSION', '0.0.2');

// Minimal GLPI version, inclusive
define("PLUGIN_MYPLUGIN_MIN_GLPI_VERSION", "10.0.0");

// Maximum GLPI version, exclusive
define("PLUGIN_MYPLUGIN_MAX_GLPI_VERSION", "11.0.99");

/**
 * Init hooks of the plugin.
 * REQUIRED
 */
function plugin_init_myplugin(): void
{
    /** @var array<string, array<string, mixed>> $PLUGIN_HOOKS */
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['myplugin'] = true;

    //add menu hook
    $PLUGIN_HOOKS[Hooks::MENU_TOADD]['myplugin'] = [
        //insert into 'plugin menu'
        'plugins' => Superasset::class,
    ];

    $PLUGIN_HOOKS[Hooks::ITEM_DELETE]['myplugin'] = [
        'Computer' => 'myplugin_computer_delete'
    ];

    $PLUGIN_HOOKS[Hooks::PRE_ITEM_FORM]['myplugin'] = [
        Superasset::class, 'preItemForm'
    ];

    $PLUGIN_HOOKS[Hooks::USE_MASSIVE_ACTION]['myplugin'] = true;

    // add new widgets to the dashboard
    $PLUGIN_HOOKS[Hooks::DASHBOARD_TYPES]['myplugin'] = [
        Dashboard::class => 'getTypes',
    ];

    // add new cards to the dashboard
    $PLUGIN_HOOKS[Hooks::DASHBOARD_CARDS]['myplugin'] = [
        Dashboard::class => 'getCards',
    ];

    //to use a js script for a specific page
    if (strpos($_SERVER['REQUEST_URI'], "ticket.form.php") !== false
        && isset($_GET['id'])) {
        $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['myplugin'][] = 'js/ticket.js.php';
    }
    //to use a js script for a specific page

    Plugin::registerClass(Superasset_Item::class, [
        "addtabon" => Computer::class
    ]);

    Plugin::registerClass(Config::class, [
        "addtabon" => \Config::class
    ]);

    Plugin::registerClass(MyPlugin_Profile::class, [
        'addtabon' => Profile::class
    ]);

    Plugin::registerClass(Superasset::class, [
        'notificationtemplates_types' => true
    ]);
}

/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array{
 *      name: string,
 *      version: string,
 *      author: string,
 *      license: string,
 *      homepage: string,
 *      requirements: array{
 *          glpi: array{
 *              min: string,
 *              max: string,
 *          }
 *      }
 * }
 */
function plugin_version_myplugin(): array
{
    return [
        'name'           => 'myplugin',
        'version'        => PLUGIN_MYPLUGIN_VERSION,
        'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
        'license'        => 'none',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_MYPLUGIN_MIN_GLPI_VERSION,
                'max' => PLUGIN_MYPLUGIN_MAX_GLPI_VERSION,
            ],
        ],
    ];
}

/**
 * Check pre-requisites before install
 * OPTIONAL
 */
function plugin_myplugin_check_prerequisites(): bool
{
    return true;
}

/**
 * Check configuration process
 * OPTIONAL
 *
 * @param bool $verbose Whether to display message on failure. Defaults to false.
 */
function plugin_myplugin_check_config(bool $verbose = false): bool
{
    // Your configuration check
    if(true)
        return true;

    if($verbose)
        __('Installed / not configured');

    return false;
}

