<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    /**
     * Display API tokens management page.
     */
    public function index(Request $request)
    {
        $tokens = $request->user()->tokens()->orderBy('created_at', 'desc')->get();

        return view('api-tokens.index', compact('tokens'));
    }

    /**
     * Create a new API token.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token = $request->user()->createToken($request->name);

        return back()->with([
            'success' => 'API token created successfully!',
            'new_token' => $token->plainTextToken,
        ]);
    }

    /**
     * Delete an API token.
     */
    public function destroy(Request $request, $tokenId)
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return back()->with('success', 'API token deleted successfully!');
    }
}
