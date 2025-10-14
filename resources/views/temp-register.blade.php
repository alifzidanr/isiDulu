<!-- resources/views/temp-register.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporary User Registration - HelpDesk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-gray-900">Temporary User Setup</h2>
                <p class="mt-2 text-sm text-red-600 font-medium">⚠️ Remove this page after setup!</p>
            </div>

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <!-- Create Default Users -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create Default Users</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Creates: Super Admin, IT Admin, and Regular User<br>
                    All with password: <strong>password</strong>
                </p>
                
                <form method="POST" action="/temp-register">
                    @csrf
                    <input type="hidden" name="action" value="create_users">
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                        Create Default Users
                    </button>
                </form>
            </div>

            <!-- Create Custom User -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create Custom User</h3>
                
                <form method="POST" action="/temp-register" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="create_single">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Access Level</label>
                        <select name="access_level" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Choose Role</option>
                            <option value="0">Super Admin (0)</option>
                            <option value="1">Admin (1)</option>
                            <option value="2">User (2)</option>
                        </select>
                    </div>

                    <button type="submit" 
                            class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700">
                        Create User
                    </button>
                </form>
            </div>

            <!-- Quick Login Links -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="font-medium text-yellow-800">Quick Login (after creating users)</h4>
                <div class="mt-2 space-y-1 text-sm">
                    <p><strong>Super Admin:</strong> superadmin@example.com / password</p>
                    <p><strong>IT Admin:</strong> itadmin@example.com / password</p>
                    <p><strong>Regular User:</strong> user@example.com / password</p>
                </div>
                <a href="/login" class="mt-3 inline-block bg-yellow-600 text-white px-4 py-2 rounded text-sm hover:bg-yellow-700">
                    Go to Login Page
                </a>
            </div>

            <div class="text-center">
                <a href="/" class="text-blue-600 hover:text-blue-500 text-sm">← Back to Home</a>
            </div>
        </div>
    </div>
</body>e
</html>