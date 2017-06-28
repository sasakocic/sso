<?php
namespace Sso;

interface PartnerInterface
{
    /**
     * Create service.
     *
     * @param array $config
     *
     * @static
     * @return self
     */
    public static function create(array $config = []);

    /**
     * Get Login url
     * example:
     * https://nk.pl/oauth2/login?
     * client_id=demo&
     * response_type=code&
     * redirect_uri=<?php echo urlencode(">&scope=BASIC_PROFILE_ROLE,EMAIL_PROFILE_ROLE,CREATE_SHOUTS_ROLE">
     *
     * @return string $url
     */
    public function getLoginUrl();

    /**
     * Get user data.
     *
     * @param array $data
     *
     * @return array
     */
    public function authenticate(array $data);
}
