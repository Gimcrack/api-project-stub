<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPromotionController extends Controller
{
    /**
     * Promote the user to admin
     * @method store
     *
     * @param User $user
     * @return JsonResponse
     */
    public function store(User $user)
    {
        $user->promoteToAdmin();

        return response()->json([],202);
    }

    /**
     * Demote the admin to a nonadmin
     * @method destroy
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user)
    {
        $user->demoteToUser();

        return response()->json([],202);
    }
}