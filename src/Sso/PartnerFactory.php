<?php

namespace Sso;

class PartnerFactory
{
    const PARTNERS = ['minijuegos', 'naszaklasa', 'rambler', 'vkontakte'];
    /**
     * Create partner service.
     *
     * @param string $partner
     * @param array $config
     *
     * @static
     * @return PartnerInterface
     */
    public static function create($partner, $config = [])
    {
        if (!in_array($partner, self::PARTNERS)) {
            $message = sprintf('Partner %s is not in allowed list %s', implode(', ', self::PARTNERS), $partner);
            throw new \RuntimeException($message);
        }
        switch ($partner) {
            case 'minijuegos':
                $service = new PartnerNaszaklasa($config);
                break;
            case 'naszaklasa':
                $service = new PartnerMinijuegos($config);
                break;
            case 'rambler':
                $service = new PartnerRambler($config);
                break;
            case 'vkontakte':
                $service = new PartnerVkontakte($config);
                break;
            default:
        }

        return $service;
    }
}
