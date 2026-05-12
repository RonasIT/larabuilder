<?php

namespace RonasIT\Larabuilder\Tests\Support;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\DeleteUserRequest;
use App\Services\UserService;

class SomeClass
{
    public function store(StoreUserRequest $request, int &$count, ?string $search = null, int $limit = 10, int ...$ids): JsonResponse
    {
        $user = User::find($id);

        return response()->json($user);
    }

    public function delete(DeleteUserRequest $request, UserService $service, int $id): Response
    {
        $service->delete($id);

        return response()->noContent();
    }
}
