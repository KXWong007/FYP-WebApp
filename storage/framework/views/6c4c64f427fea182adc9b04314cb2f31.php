<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: #d5d5d5;
        }

        .main-container {
            display: flex;
            height: 100vh;
            width: 100%;
        }

        .image-section {
            display: flex;
            width: 50%;
            background-image: url('/images/wine_table.jpeg');
            background-size: cover;
            background-position: center;
            justify-content: flex-start; /* Align text to the left */
            align-items: flex-end; /* Align text to the bottom */
            padding: 2rem;
            color: #fff;
            position: relative;
        }

        .image-section .text-overlay {
        font-size: 3rem;
        font-family: 'Playfair Display', serif;
        text-align: left;
        position: absolute;        /* New addition for fixed positioning */
        bottom: 20px;              /* Positions the text near the bottom */
        left: 20px;                /* Positions the text to the left side */
        line-height: 1.2;          /* Keeps spacing between STAFF and PORTAL */
        color: #fff;
        }       

        .login-container {
            width: 50%;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #2a2a2a; /* Updated to have a darker background matching the desired theme */
            border-radius: 0.5rem;
        }

        .login-header {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 20px;
        }

        .login-header::before,
        .login-header::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #b29e72;
            margin: 0 15px;
        }

        .login-header span {
            color: #d5d5d5;
            font-size: 1.5rem;
            font-family: 'Playfair Display', serif;
            text-align: center;
        }

        .decorative-line {
            width: 30px;
            height: 1px;
            background: #b29e72;
            margin: 0 auto;
        }

        .login-title {
            font-size: 1rem;
            color: #b29e72;
            margin-top: 0.5rem;
            text-align: center;
        }

        .login-button {
            background-color: #b29e72;
        }

        .login-button:hover {
            background-color: #9c8963;
        }

        label,
        h1 {
            color: #d5d5d5;
        }

        .remember-me-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #d5d5d5; /* Matching the text color to dark theme */
        }

        .bg-dark-form {
            background-color: #2a2a2a; /* Dark theme for form container */
            color: #d5d5d5; /* Ensuring text color is readable */
            border: 1px solid #444444; /* Border to make it stand out */
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Left Image Section -->
        <!-- Left Image Section -->
    <div class="image-section">
    <div class="text-overlay">
    STAFF <br> PORTAL   <!-- STAFF and PORTAL now on separate lines -->
</div>

        </div>
        <!-- Login Section -->
        <div class="login-container">
            <div class="login-header">
                <span>LOGIN</span>
            </div>
            <div class="decorative-line"></div>
            <div class="login-title">A Renowned Club Since 1876</div>
            <div class="w-full bg-dark-form rounded-lg shadow md:mt-0 sm:max-w-md xl:p-0">
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8 dark-background">
                    <h1 class="text-xl font-bold leading-tight tracking-tight md:text-2xl">
                        Sign in to your account
                    </h1>
                    <form class="space-y-4 md:space-y-6" method="post" action="<?php echo e(route('login.action')); ?>">
                        <?php echo csrf_field(); ?>
                        <?php if($errors->any()): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <ul>
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><span class="block sm:inline"><?php echo e($error); ?></span></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <title>Close</title>
                                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                                </svg>
                            </span>
                        </div>
                        <?php endif; ?>
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium">Your email</label>
                            <input type="email" name="email" id="email" class="bg-gray-700 border border-gray-600 text-white sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500" placeholder="name@example.com" required="">
                        </div>
                        <div>
                            <label for="password" class="block mb-2 text-sm font-medium">Password</label>
                            <input type="password" name="password" id="password" placeholder="••••••••" class="bg-gray-700 border border-gray-600 text-white sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 placeholder-gray-400 focus:ring-blue-500 focus:border-blue-500" required="">
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="remember-me-checkbox">
                                <input type="checkbox" name="remember" id="remember" aria-describedby="remember" class="w-4 h-4 border border-gray-300 rounded bg-gray-700 focus:ring-3 focus:ring-primary-300 dark:ring-offset-gray-800">
                                <label for="remember">Remember me</label>
                            </div>
                            <a href="#" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-500">Forgot password?</a>
                        </div>
                        <button type="submit" class="flex w-full justify-center rounded-md px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm login-button focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Sign in</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\Users\kxwon\Desktop\Swinburne CS\2024 Sem 2\SWE40001 - SEPA\FYP\FYP-WebApp\resources\views/auth/login.blade.php ENDPATH**/ ?>