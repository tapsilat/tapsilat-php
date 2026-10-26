<?php

namespace Tapsilat\Models;

/**
 * A newly created Tapsilat Elements session, as POST /elements/sessions returns it.
 *
 * Hand getClientSecret() to the app that opens the card sheet; it is returned only here. Do not
 * log or store it. var_dump() and print_r() show it as "[redacted]", while toArray() and
 * json_encode() keep it so that it can be sent to the app.
 */
class ElementsSessionWithSecret extends ElementsSession
{
    /**
     * @return string|null The secret the app opens the card sheet with
     */
    public function getClientSecret()
    {
        return $this->field('client_secret');
    }

    /**
     * @return string|null The Elements host the app talks to, when the response names one
     */
    public function getElementsBaseUrl()
    {
        return $this->field('elements_base_url');
    }
}
