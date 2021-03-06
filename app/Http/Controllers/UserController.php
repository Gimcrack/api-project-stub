<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{

    /**
     * Get a listing of the resource
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(User::all(), 200);
    }

    /**
     * Store the new user
     * @method store
     *
     * @param NewUserRequest $request
     * @return JsonResponse
     */
    public function store(NewUserRequest $request)
    {
        $user = User::create( $request->validated() );

        $user->password = bcrypt(request('password'));
        $user->save();

        return response()->json([], 201);
    }

    /**
     * Update the selected user record
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update( $request->validated() );

        return response()->json([],202);
    }

    /**
     * Destroy the selected user record
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user)
    {
        if ( $user->isAdmin() ) {

            return response()->json([
                'errors' => true,
                'message' => 'Cannot delete admin account'
            ], 403);
        }

        $user->delete();

        return response()->json([], 202);
    }
}