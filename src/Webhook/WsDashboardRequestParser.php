<?php

namespace App\Webhook;

use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher\HostRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\IpsRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\IsJsonRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\PathRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\QueryParameterRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Component\Webhook\Client\AbstractRequestParser;
use Symfony\Component\Webhook\Exception\RejectWebhookException;

final class WsDashboardRequestParser extends AbstractRequestParser
{
    // TODO: Refactor into enums
    const ACTION_REGISTRATION = 'registration';
    const ACTION_VISIT = 'visit';
    const ACTIONS = [
        self::ACTION_REGISTRATION,
        self::ACTION_VISIT
    ];

    // TODO: Allow passing via config
    const ORIGIN_COLLERYS = 'collerys';
    const ORIGIN_DUFTZ = 'duftz';
    const ORIGIN_BELEGBOTE = 'belegbote';
    const ORIGIN_GREETIX = 'greetix';
    const ORIGINS = [
        self::ORIGIN_COLLERYS,
        self::ORIGIN_DUFTZ,
        self::ORIGIN_BELEGBOTE,
        self::ORIGIN_GREETIX,
    ];

    protected function getRequestMatcher(): RequestMatcherInterface
    {
        return new ChainRequestMatcher([
            new IsJsonRequestMatcher(),
            new IpsRequestMatcher(['127.0.0.1']),
            new HostRequestMatcher('.local'),
            new MethodRequestMatcher('POST'),
            new QueryParameterRequestMatcher(['action'])
        ]);
    }

    /**
     * @throws JsonException
     */
    protected function doParse(Request $request, #[\SensitiveParameter] string $secret): ?RemoteEvent
    {
        // Validate the request against $secret.
        // $authToken = $request->headers->get('X-Authentication-Token');
        //
        // if ($authToken !== $secret) {
        //     throw new RejectWebhookException(Response::HTTP_UNAUTHORIZED, 'Invalid authentication token.');
        // }

        // Validate action
        // TODO: Ideally, refactor into separate endpoints for each action
        // TODO: For now, define actions as enum rather than constants
        if (!in_array($request->get('action'), self::ACTIONS)) {
            throw new RejectWebhookException(Response::HTTP_BAD_REQUEST, 'Request parameters contain invalid values.');
        }

        // Validate the request payload.
        if (!$request->getPayload()->has('origin')) {
            throw new RejectWebhookException(Response::HTTP_BAD_REQUEST, 'Request payload does not contain required fields.');
        }

        $origin = mb_strtolower($request->getPayload()->getString('origin'));
        if (!in_array($origin, self::ORIGINS)) {
            throw new RejectWebhookException(Response::HTTP_BAD_REQUEST, 'Request payload contains invalid values.');
        }

        // Parse the request payload and return a RemoteEvent object.
        $payload = $request->getPayload();

        // TODO: Custom Event by action, ie RegistrationEvent and VisitEvent?

        return new RemoteEvent(
            $request->get('action'),
            $origin,
            $payload->all(),
        );
    }
}
