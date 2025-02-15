<?php

namespace Rammewerk\Http;

use Closure;
use DateTimeImmutable;
use InvalidArgumentException;
use Rammewerk\Component\Hydrator\Hydrator;
use Rammewerk\Component\Hydrator\PropertyTypes\PropertyHandler;
use RuntimeException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

class Request extends SymfonyRequest {

    private ?FlashBagInterface $flash = null;



    public function setFlashBag(FlashBagInterface $getFlashBag): void {
        $this->flash = $getFlashBag;
    }


    /*
    |--------------------------------------------------------------------------
    | Domain and URI checks
    |--------------------------------------------------------------------------
    */


    /** Get the current path info for the request. Example: 'profile/settings'*/
    public function path(): string {
        return trim($this->getPathInfo(), '/');
    }



    /** Get the registered domain. Domain without subdomain */
    public function domainName(): string {
        $parts = explode('.', $this->getHost());
        return implode('.', array_slice($parts, -2));
    }



    /** Get the subdomain. Example: 'subdomain' from 'subdomain.domain.com'*/
    public function subdomain(): string {
        return implode('.', explode('.', $this->getHost(), -2));
    }



    /** Check if subdomain is the same as the given string */
    public function isSubdomain(string $subdomain): bool {
        return strtolower($this->subdomain()) === strtolower($subdomain);
    }



    /*
    |--------------------------------------------------------------------------
    | Get inputs, server, file and cookie data
    |--------------------------------------------------------------------------
    */

    public function input(string $key): mixed {
        if ($this->query->has($key)) {
            return $this->query->all()[$key];
        }
        if ($this->request->has($key)) {
            return $this->request->all()[$key];
        }
        return null;
    }



    /**
     * Get all post and query data
     *
     * @return array<string, mixed>
     */
    public function all(): array {
        return array_replace_recursive($this->request->all(), $this->query->all());
    }



    /**
     * @param string $name
     *
     * @return UploadedFile|null
     */
    public function file(string $name): ?UploadedFile {
        $file = $this->files->get($name);
        return $file instanceof UploadedFile ? $file : null;
    }



    /*
    |--------------------------------------------------------------------------
    | CSRF check
    |--------------------------------------------------------------------------
    */

    public function generateCsrfToken(string $tokenId = 'default'): string {
        $csrfManager = new CsrfTokenManager();
        return $csrfManager->getToken($tokenId)->getValue();
    }



    /**
     * Validate CSRF token using Symfony's CsrfTokenManager.
     * Expects the token to be provided in the request input (default '_csrf_token')
     * and uses a token ID (default 'default').
     *
     * @param string $tokenId    The CSRF token id/intention.
     * @param string $inputField The request input name for the token.
     *
     * @return void
     * @throws RuntimeException if token is missing or invalid.
     */
    public function validateCsrfToken(string $tokenId = 'default', string $inputField = '_csrf_token'): void {
        $tokenValue = $this->inputString($inputField) ?? '';
        $csrfToken = new CsrfToken($tokenId, $tokenValue);
        $csrfManager = new CsrfTokenManager();
        if (!$csrfManager->isTokenValid($csrfToken)) {
            throw new InvalidCsrfTokenException('Invalid CSRF token.');
        }
    }



    /*
    |--------------------------------------------------------------------------
    | Type safe inputs
    |--------------------------------------------------------------------------
    */

    private function inputScalar(string $key): float|bool|int|string|null {
        return $this->query->get($key) ?? $this->request->get($key) ?? null;
    }



    public function inputString(string $key): ?string {
        $v = $this->inputScalar($key);
        return is_scalar($v) ? (string)$v : null;
    }



    public function inputInt(string $key): ?int {
        $v = $this->inputScalar($key);
        return is_numeric($v) ? (int)round((float)$v) : null;
    }



    public function inputFloat(string $key): ?float {
        $v = $this->inputScalar($key);
        return is_scalar($v) ? (float)$v : null;
    }



    public function inputBool(string $key): bool {
        return filter_var($this->inputScalar($key), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }



    /**
     * @param string $key
     *
     * @return mixed[]|null
     */
    public function inputArray(string $key): ?array {
        $v = $this->input($key);
        return is_array($v) ? $v : null;
    }



    public function inputDateTime(string $key, ?string $format = null, bool $throwOnError = false): ?DateTimeImmutable {

        $v = $this->inputString($key);
        if (empty($v)) return null;

        try {
            $dateTime = ($format) ? DateTimeImmutable::createFromFormat($format, $v) : new DateTimeImmutable($v);
            if ($dateTime === false && $throwOnError) {
                throw new InvalidArgumentException("Unable to parse date with the given format: $format");
            }
            return $dateTime ?: null;
        } catch (\Exception $e) {
            if ($throwOnError) {
                throw new InvalidArgumentException("Unable to parse date: " . $e->getMessage());
            }
            return null;
        }
    }



    public function inputEmail(string $string): ?string {
        $v = $this->inputString($string);
        if (empty($v)) return null;
        if (!filter_var(trim($v), FILTER_VALIDATE_EMAIL)) return null;
        return $v;
    }



    /*
    |--------------------------------------------------------------------------
    | Type Entity Decoder
    |--------------------------------------------------------------------------
    */


    /**
     * Hydrates a given entity class with request data, allowing for field mapping, required field validation,
     * and guarding fields from being set.
     *
     * @template T of object
     * @param class-string<T>|T $entity                 The fully qualified class name of the entity to hydrate.
     * @param Closure(DecodeConfig):void|null $settings A closure to configure mapping, required fields, and guarded fields.
     *
     * @return T
     * @throws InvalidArgumentException If a required field is missing in the request data.
     * @noinspection PhpDocSignatureInspection
     */
    public function decode(string|object $entity, ?Closure $settings = null) {

        $check = new DecodeConfig();
        if ($settings) {
            ($settings)($check);
        }
        [$inputMap, $required, $protected] = $check->getSettings();

        return new Hydrator($entity)->hydrate(callback: function (PropertyHandler $handler) use ($inputMap, $required, $protected) {

            $value = isset($protected[$handler->name])
                ? null
                : $this->input($inputMap[$handler->name] ?? $handler->name);

            if (empty($value) && isset($required[$handler->name])) {
                throw new InvalidArgumentException('Missing required property: ' . $handler->name);
            }

            return $value;
        });

    }



    /*
    |--------------------------------------------------------------------------
    | Flash messages
    |--------------------------------------------------------------------------
    */

    public function flash(string $type, string $message): void {
        if (!$this->flash) return;
        $this->flash->add($type, $message);
    }



    /**
     * Returns all flash messages of a given type.
     *
     * @return array<string, string[]>
     */
    public function getFlashMessages(): array {
        if (!$this->flash) return [];
        /** @var array<string, string[]> $flash */
        return $this->flash->all();
    }



    /*
    |--------------------------------------------------------------------------
    | HTMX
    |--------------------------------------------------------------------------
    */

    public function htmxBoosted(): bool {
        return $this->headers->get('HX-Boosted') === 'true';
    }



    public function htmxCurrentUrl(): ?string {
        return $this->headers->get('HX-Current-URL');
    }



    public function htmxHistoryRestoreRequest(): bool {
        return $this->headers->get('HX-History-Restore-Request') === 'true';
    }



    public function htmxPromptResponse(): ?string {
        return $this->headers->get('HX-Prompt');
    }



    public function htmxRequest(): bool {
        return $this->headers->get('HX-Request') === 'true';
    }



    public function htmxTargetId(): ?string {
        return $this->headers->get('HX-Target');
    }



    public function htmxTriggerName(): ?string {
        return $this->headers->get('HX-Trigger-Name');
    }



    public function htmxTriggerId(): ?string {
        return $this->headers->get('HX-Trigger');
    }



}