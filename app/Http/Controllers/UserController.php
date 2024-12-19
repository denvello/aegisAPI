<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // Validate request input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'name' => 'required|string|min:3|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Insert new user record
        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'name' => $request->name,
        ]);

        // Send confirmation email to the user
        Mail::raw('Your account has been created successfully, thank you!', function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Account Creation Confirmation');
        });

        // Send notification email to the administrator
        Mail::raw("A new user has registered:\n\nName: {$user->name}\nEmail: {$user->email}", function ($message) {
            $message->to('admin@aegis.com') //saya berikan contoh kirim email ke admin@aegis.com
                    ->subject('New User Registration');
        });

        // Return user details (exclude password)
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'created_at' => $user->created_at->toIso8601String(),
        ], 201);
    }

    public function index(Request $request)
    {
        // Retrieve request inputs
        $search = $request->input('search');
        $page = $request->input('page', 1);
        $sortBy = $request->input('sortBy', 'created_at');

        // Validate sortBy parameter
        if (!in_array($sortBy, ['name', 'email', 'created_at'])) {
            $sortBy = 'created_at';
        }

        // Build the query
        $query = User::query()
            ->where('active', true) // Ensure only active users are retrieved
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortBy);

        // Paginate the results
        $users = $query->paginate(10, ['*'], 'page', $page);

        // Transform the results
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'created_at' => $user->created_at->toIso8601String(),
                'orders_count' => $user->orders()->count(),
            ];
        });

        // Return the response
        return response()->json([
            'page' => $users->currentPage(),
            'users' => $users->items(),
        ]);
    }
}
