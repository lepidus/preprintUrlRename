<?php

/**
 * @file PreprintUrlRenamePlugin.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PreprintUrlRenamePlugin
 * @brief Renames the "preprint" part of OPS URLs to something else.
 */

namespace APP\plugins\generic\preprintUrlRename;

use APP\core\Application;
use PKP\config\Config;
use PKP\core\Registry;
use PKP\facades\Locale;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\core\PKPRequest;

class PreprintUrlRenamePlugin extends GenericPlugin
{
    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null): bool
    {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled($mainContextId)) {
                // When OPS generates URLs referring to 'preprint', change it to 'postprint'
                Hook::add('PKPPageRouter::url', function (string $hookName, array $params): bool {
                    if ($page == 'preprint') {
                        $page = 'postprint';
                    }
                    return Hook::CONTINUE;
                });

                // When OPS receives requests for 'postprint', map it to the preprint handler
                Hook::add('LoadHandler', function (string $hookName, array $params): bool {
                    $page = &$params[0];
                    $sourceFile = &$params[2];

                    if ($page != 'postprint') {
                        return Hook::CONTINUE;
                    }

                    $page = 'preprint';
                    $sourceFile = 'pages/preprint/index.php';

                    return Hook::CONTINUE;
                });
            }
            return true;
        }
        return false;
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName()
    {
        return 'Preprint URL rename plugin';
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription()
    {
        return 'Renames the "preprint" part of OPS URLs to something else.';
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\preprintUrlRename\PreprintUrlRenamePlugin', '\PreprintUrlRenamePlugin');
}
