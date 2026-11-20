<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt using Phone 1 or Email.
     */
    public function login(Request $request)
    {
        $loginInput = trim((string) ($request->input('login') ?? $request->input('email') ?? $request->input('phone1')));
        $password = (string) $request->input('password');

        if (empty($loginInput) || empty($password)) {
            throw ValidationException::withMessages([
                'login' => 'Phone number or email and password are required.',
            ]);
        }

        $remember = $request->boolean('remember');

        // Check if input is an email address
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'phone1';

        $credentials = [
            $field => $loginInput,
            'password' => $password,
        ];

        // Attempt login
        $authenticated = Auth::attempt($credentials, $remember);

        // Fallback: If not email, test if input matches email in db as well
        if (!$authenticated && !$isEmail) {
            $authenticated = Auth::attempt(['email' => $loginInput, 'password' => $password], $remember);
        }

        // Fallback: Test if input matches phone1 without leading zeros or spaces
        if (!$authenticated && !$isEmail) {
            $cleaned = preg_replace('/[^0-9]/', '', $loginInput);
            if (!empty($cleaned)) {
                $userByPhone = User::whereRaw("REPLACE(REPLACE(REPLACE(phone1, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleaned}%"])->first();
                if ($userByPhone && \Illuminate\Support\Facades\Hash::check($password, $userByPhone->password)) {
                    Auth::login($userByPhone, $remember);
                    $authenticated = true;
                }
            }
        }

        if ($authenticated) {
            /** @var User $user */
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw ValidationException::withMessages([
                    'login' => 'Your account has been deactivated. Please contact Super Admin.',
                ]);
            }

            $request->session()->regenerate();

            // Redirect: If superadmin or has dashboard read access, go to dashboard
            if ($user->isSuperAdmin() || $user->hasPermission('dashboard', 'read')) {
                return redirect()->intended(route('dashboard'))
                    ->with('success', 'Welcome back, ' . $user->name . '!');
            }

            // Otherwise redirect to first accessible module
            $targetRoute = $this->getFirstPermittedRoute($user);
            return redirect()->intended(route($targetRoute))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        throw ValidationException::withMessages([
            'login' => 'The provided credentials do not match our records.',
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Resolve the first permitted route for a user who does not have dashboard access.
     */
    protected function getFirstPermittedRoute(User $user): string
    {
        return $user->getFirstPermittedRoute();
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
