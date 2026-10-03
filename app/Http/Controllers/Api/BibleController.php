<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\LookupBiblePassageRequest;
use App\Http\Requests\StoreScriptureRequest;
use App\Http\Resources\BiblePassageResource;
use App\Http\Resources\ScriptureResource;
use App\Models\Scripture;
use App\Services\Bible\BibleException;
use App\Services\Bible\BibleService;
use App\Services\Bible\BibleTranslation;
use App\Services\Bible\ReferenceParseException;
use App\Services\Bible\TranslationUnavailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class BibleController
{
    public function __construct(
        private readonly BibleService $service,
    ) {}

    public function translations(): JsonResponse
    {
        return response()->json([
            'data' => array_map(
                static fn (BibleTranslation $translation): array => $translation->toArray(),
                $this->service->translations(),
            ),
            'default' => $this->service->defaultTranslation(),
            'provider' => $this->service->providerName(),
        ]);
    }

    public function passages(LookupBiblePassageRequest $request): BiblePassageResource|JsonResponse
    {
        try {
            $passage = $this->service->passage(
                (string) $request->validated('reference'),
                $request->validated('translation'),
            );
        } catch (ReferenceParseException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'error' => 'invalid_reference',
            ], 422);
        } catch (TranslationUnavailableException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'error' => 'translation_unavailable',
            ], 422);
        } catch (BibleException $exception) {
            return response()->json([
                'message' => 'The Bible service is temporarily unavailable. Please try again.',
                'error' => 'provider_unavailable',
            ], 503);
        }

        return new BiblePassageResource($passage);
    }

    public function save(StoreScriptureRequest $request): JsonResource
    {
        return new ScriptureResource(Scripture::create($request->validated()));
    }
}
