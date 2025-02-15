<?php

namespace Rammewerk\Http;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse {


    public function redirect(string $url, int $status = 302, array $headers = []): RedirectResponse {
        return new RedirectResponse($url, $status, $headers);
    }



    public function htmxStopPolling(): static {
        $this->setStatusCode(286);
        return $this;
    }



    public function htmxRedirect(string $location, int $status_code = 302): static {
        $this->headers->set('HX-Redirect', $location);
        $this->setStatusCode($status_code);
        return $this;
    }



    public function htmxRefresh(): static {
        $this->headers->set('HX-Refresh', 'true');
        $this->setStatusCode(200);
        return $this;
    }



    public function htmxLocation(string $path, ?array $context = null): static {
        if (!empty($context)) {
            $path = json_encode(array_merge(['path' => $path], $context));
        }
        $this->headers->set('HX-Location', $path);
        return $this;
    }



    public function htmxPushUrl(string $url): static {
        $this->headers->set('HX-Push-Url', $url);
        return $this;
    }



    public function htmxReplaceUrl(string $url): static {
        $this->headers->set('HX-Replace-Url', $url);
        return $this;
    }



    public function htmxReswap(string $option): static {
        $this->headers->set('HX-Reswap', $option);
        return $this;
    }



    public function htmxRetarget(string $selector): static {
        $this->headers->set('HX-Retarget', $selector);
        return $this;
    }



    /** @param string|mixed[] $events */
    public function htmxTriggers(string|array $events): static {
        return $this->_setHtmxTriggers('HX-Trigger', $events);
    }



    /** @param string|mixed[] $events */
    public function htmxAfterSettleTriggers(string|array $events): static {
        return $this->_setHtmxTriggers('HX-Trigger-After-Settle', $events);
    }



    /** @param string|mixed[] $events */
    public function htmxAfterSwapTriggers(string|array $events): static {
        return $this->_setHtmxTriggers('HX-Trigger-After-Swap', $events);
    }



    /** @param string|mixed[] $value */
    private function _setHtmxTriggers(string $key, string|array $value): static {
        if ($value === '' || $value === []) {
            throw new \InvalidArgumentException("Trigger value MUST be an non-empty string or array");
        }

        if (is_array($value)) {
            try {
                $value = json_encode($value, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new \LogicException('Unable to encode triggers: ' . $e->getMessage());
            }
        }

        $this->headers->set($key, $value);
        return $this;
    }



    public static function pageNotFound(string $message = 'Page not found'): static {
        return new static($message, 404, ['Content-Type' => 'text/plain; charset=utf-8']);
    }


}