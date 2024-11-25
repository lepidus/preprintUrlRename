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
                Hook::add('PKPPageRouter::url', [$this, 'renameUrlPages']);
                Hook::add('LoadHandler', [$this, 'mapRenamedUrlPagesToHandler']);
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

    // When OPS generates URLs referring to 'preprint', change it to 'postprint'
    public function renameUrlPages(string $hookName, array $params)
    {
        $page = &$params['page'];
        $renamingMap = [
            'preprint' => 'postprint',
            'preprints' => 'postprints'
        ];

        if (isset($renamingMap[$page])) {
            $page = $renamingMap[$page];
        }

        return Hook::CONTINUE;
    }

    // When OPS receives requests for 'postprint', map it to the preprint handler
    public function mapRenamedUrlPagesToHandler(string $hookName, array $params)
    {
        $page = &$params[0];
        $sourceFile = &$params[2];
        $mapRenamedPages = [
            'postprint' => ['page' => 'preprint', 'sourceFile' => 'pages/preprint/index.php'],
            'postprints' => ['page' => 'preprints', 'sourceFile' => 'pages/preprints/index.php'],
        ];

        if (isset($mapRenamedPages[$page])) {
            $renamedPageMap = $mapRenamedPages[$page];
            $page = $renamedPageMap['page'];
            $sourceFile = $renamedPageMap['sourceFile'];
        }

        return Hook::CONTINUE;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\preprintUrlRename\PreprintUrlRenamePlugin', '\PreprintUrlRenamePlugin');
}
