<?php

namespace Tapsilat\Concerns;

use Tapsilat\Models\ElementsSession;
use Tapsilat\Models\ElementsSessionCreateRequest;
use Tapsilat\Models\ElementsSessionWithSecret;

/**
 * Tapsilat Elements sessions for the native Android and iOS card sheet. Create a session on your
 * backend, hand only its client secret to the app, and read the outcome back with
 * getElementsSession(). Errors on these routes carry a stable code and a retryable flag.
 */
trait ElementsMethods
{
    /**
     * Creates a session with POST /elements/sessions. The result carries the client secret: pass it
     * to the app that opens the card sheet, and do not log or store it.
     */
    public function createElementsSession(ElementsSessionCreateRequest $request)
    {
        $response = $this->makeRequest('POST', '/elements/sessions', null, $request->toArray());
        return new ElementsSessionWithSecret($response);
    }

    /**
     * Returns the session's authoritative state and outcome with GET /elements/sessions/{id}. Read it
     * on your backend before fulfilling, rather than trusting what the app reports.
     */
    public function getElementsSession($id)
    {
        return new ElementsSession($this->makeRequest('GET', $this->path('/elements/sessions/%s', $id)));
    }

    /**
     * Cancels a session that is still waiting for a card with POST /elements/sessions/{id}/cancel. A
     * session that is confirming or has completed comes back unchanged.
     */
    public function cancelElementsSession($id)
    {
        return new ElementsSession($this->makeRequest('POST', $this->path('/elements/sessions/%s/cancel', $id)));
    }
}
