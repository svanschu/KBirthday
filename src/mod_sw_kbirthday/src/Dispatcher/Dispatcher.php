<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

namespace SchuWeb\Module\Birthday\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Kunena\Forum\Libraries\Forum\KunenaForum;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_articles_latest
 *
 * @since  _BUMP_VERSION_
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   _BUMP_VERSION_
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        Log::addLogger(array('text_file' => 'mod_sw_kbirthday.errors.php'), Log::ALL, 'mod_sw_kbirthday');

        $kunenaConnection = $data['params']->get('connection');
        $integration      = $data['params']->get('integration');

        $minCBVersion     = '2.0.0';
        $minKunenaVersion = '6.0.0';

        if ($integration == 'kunena' || $kunenaConnection == 'forum') {
            if (ComponentHelper::isInstalled('com_kunena') == 0) {
                $data['error'] = Text::sprintf('SCHUWEB_BIRTHDAY_NOT_INSTALLED', $minKunenaVersion);
                return $data;
            } elseif (!ComponentHelper::isEnabled('com_kunena')) {
                $data['error'] = Text::_('SCHUWEB_BIRTHDAY_NOT_ENABLED');
                return $data;
            } elseif (!version_compare(KunenaForum::version(), $minKunenaVersion, '>=')) {
                // Kunena is not installed or enabled
                $data['error'] = Text::sprintf('SCHUWEB_BIRTHDAY_NOT_INSTALLED', $minKunenaVersion);
                return $data;
            } elseif (!KunenaForum::enabled()) {
                // Kunena is not online, DO NOT use Kunena!
                $data['error'] = Text::_('SCHUWEB_BIRTHDAY_NOT_ENABLED');
                return $data;
            }
        }

        if ($integration == 'jomsocial' || $kunenaConnection == 'jomsocial') {
            //TODO check if version is correct and installed
        }

        if ($integration == 'comprofiler' || $kunenaConnection == 'communitybuilder') {
            if (!ComponentHelper::isEnabled("com_comprofiler")) {
                $data['error'] = Text::_("SWBIRTHDAY_CB_NOTINSTALLED_ENABLED");
                return $data;
            }

            $db    = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select('manifest_cache');
            $query->from($db->quoteName('#__extensions'));
            $query->where('element = "com_comprofiler"');
            $db->setQuery($query);
            $manifest = json_decode($db->loadResult(), true);

            if (!version_compare($manifest['version'], $minCBVersion, '>=')) {
                $data['error'] = Text::sprintf("SWBIRTHDAY_CB_WRONG_VERSION", $minCBVersion);
                return $data;
            }
        }

        #$res = BirthdayHelper::loadHelper($data['params']);
        $birthdayHelper = $this->getHelperFactory()
            ->getHelper($data['params']->get('connection').'Helper', $data['params']->toArray());
        $birthdayHelper->setIntegration(
            $this->getHelperFactory()
                ->getHelper('Integration\\'.$data['params']->get('integration').'Helper', $data['params']->toArray())
        );
        $res = $birthdayHelper
            ->getUserBirthday($data['params']);

        if (empty ($res))
            $res = Text::_('SCHUWEB_BIRTHDAY_NOUPCOMING');

        $data['res'] = $res;

        return $data;
    }
}
