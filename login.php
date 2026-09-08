<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Civentral</title>
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <style type="text/tailwindcss">
  @theme {
    --color-brand-light: #EEF5FF;
    --color-brand-border: #B4D4FF;
    --color-brand-medium: #86B6F6;
    --color-brand-dark: #176B87;
  }
</style>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-white min-h-screen font-sans antialiased selection:bg-brand-medium selection:text-white">

  <div class="min-h-screen flex flex-col md:flex-row relative">
    
    <div class="hidden md:block md:w-1/2 lg:w-3/5 bg-[url(assets/images/building-bg.jpg)] bg-cover bg-left bg-no-repeat mix-blend-multiply relative">
      <div class="absolute inset-0 bg-gradient-to-r from-transparent to-white"></div>
    </div>

    <div class="flex-1 flex flex-col justify-between p-8 sm:p-12 md:p-16 lg:p-24 bg-white z-10">
      
      <div></div>

      <div class="w-full max-w-md mx-auto space-y-6 my-auto relative">
  
        <div class="w-full space-y-6">
          <div class="flex flex-col items-center justify-center text-center pb-4 w-full">
            <img src="assets/images/logo.png" alt="Civentral Graphic" class="h-24 w-auto object-contain mb-3">
            <span class="text-4xl font-black text-brand-medium tracking-[0.25em] uppercase font-sans">
              Civentral
            </span>
          </div>
          
          <div class="flex flex-col items-center md:items-start text-center md:text-left space-y-2">
            <span class="text-xs font-bold tracking-widest text-gray-400 uppercase">Employee Access</span>
            <h1 class="text-3xl font-extrabold text-gray-600 tracking-tight">Sign in to your office</h1>
            <p class="text-xs text-gray-500">Enter your LGU-issued credentials to continue.</p>
          </div>
        </div>

        <div class="relative min-h-[4px]">
          <div id="statusModal" class="hidden absolute left-0 right-0 -top-2 z-20 border rounded-lg p-4 items-start space-x-3 shadow-md transition-all duration-300">
            <div id="modalIcon" class="mt-0.5"></div>
            <p id="modalMessage" class="text-xs font-medium leading-relaxed"></p>
          </div>
        </div>

        <form id="loginForm" class="space-y-4 pt-2" onsubmit="handleLogin(event)">
    
        <div class="space-y-1.5">
          <label for="employeeId" class="text-xs font-semibold text-gray-500">LGU Employee ID</label>
          <div class="relative flex items-center">
            <span class="absolute left-4 text-gray-400">
              <i class="fa-solid fa-user-tie text-sm"></i>
            </span>
            <input 
              type="text" 
              id="employeeId" 
              placeholder="e.g. SADM-2026-001 or email" 
              required
              class="w-full pl-11 pr-4 py-3 bg-white border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-brand-medium focus:ring-1 focus:ring-brand-medium transition"
            />
          </div>
        </div>

        <div class="space-y-1.5">
          <label for="password" class="text-xs font-semibold text-gray-500">Password</label>
          <div class="relative flex items-center">
            <span class="absolute left-4 text-gray-400">
              <i class="fa-solid fa-key text-sm"></i>
            </span>
            <input 
              type="password" 
              id="password" 
              placeholder="••••••••••••••••" 
              required
              class="w-full pl-11 pr-11 py-3 bg-white border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-brand-medium focus:ring-1 focus:ring-brand-medium transition"
            />
            <button 
              type="button" 
              onclick="togglePasswordVisibility()" 
              class="absolute right-4 text-gray-400 hover:text-gray-600 focus:outline-none"
            >
              <i id="passwordIcon" class="fa-solid fa-eye-slash text-sm"></i>
            </button>
          </div>
        </div>

        <div class="flex items-center justify-between pt-1">
          <label class="flex items-center space-x-2 cursor-pointer select-none">
            <input 
              type="checkbox" 
              class="w-4 h-4 text-brand-medium border-gray-300 rounded focus:ring-brand-medium accent-brand-medium"
            />
            <span class="text-xs text-gray-500">Keep me signed in</span>
          </label>
          <button type="button" onclick="openForgotModal()" class="text-xs font-semibold text-brand-medium hover:underline focus:outline-none">Forgot password?</button>
        </div>

        <button 
          type="submit" 
          class="w-full py-3 px-4 bg-brand-medium hover:bg-opacity-90 text-white font-medium rounded-lg text-sm transition shadow-sm focus:outline-none"
        >
          Sign in
        </button>
      </form>

      <div class="relative flex py-2 items-center">
        <div class="flex-grow border-t border-gray-200"></div>
        <span class="flex-shrink mx-4 text-xs font-semibold text-gray-400 tracking-wider">OR</span>
        <div class="flex-grow border-t border-gray-200"></div>
      </div>

      <a 
        href="index.php"
        class="inline-block text-center w-full py-3 px-4 bg-white hover:bg-brand-medium hover:text-white text-brand-medium font-medium border border-brand-medium rounded-lg text-sm transition focus:outline-none"
      >
        Back to Home
      </a>

  </div>

      <div class="text-center pt-8">
        <p class="text-[10px] md:text-xs font-bold text-gray-400 tracking-wider uppercase max-w-sm mx-auto leading-relaxed">
          DEPT ACCESS ONLY - UNAUTHORIZED USE IS LOGGED & PROSECUTABLE UNDER RA 8792
        </p>
      </div>

    </div>
  </div>

  <!-- 2FA OTP Verification Modal -->
  <div id="otpModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs transition-all duration-300 opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 sm:p-8 transform scale-95 transition-all duration-300 border border-slate-100 relative">
      
      <!-- Close / Back Button -->
      <button type="button" onclick="closeOtpModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition cursor-pointer">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>

      <div class="text-center space-y-3 mb-6">
        <div class="h-14 w-14 rounded-2xl bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark mx-auto shadow-xs">
          <i class="fa-solid fa-shield-halved text-2xl"></i>
        </div>
        <h3 class="text-xl font-black text-slate-800 tracking-tight">Two-Factor Verification</h3>
        <p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed">
          We sent a 6-digit security code to <strong id="otpMaskedEmail" class="text-brand-dark font-mono">your email</strong>.
        </p>
      </div>

      <!-- OTP Form -->
      <form id="otpForm" onsubmit="handleVerifyOTP(event)" class="space-y-6">
        
        <!-- 6 Input boxes -->
        <div class="flex justify-between items-center gap-2 max-w-xs mx-auto">
          <input type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" class="otp-input w-11 h-12 text-center text-xl font-bold font-mono text-brand-dark bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/20 focus:outline-none transition" required autofocus />
          <input type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" class="otp-input w-11 h-12 text-center text-xl font-bold font-mono text-brand-dark bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/20 focus:outline-none transition" required />
          <input type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" class="otp-input w-11 h-12 text-center text-xl font-bold font-mono text-brand-dark bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/20 focus:outline-none transition" required />
          <input type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" class="otp-input w-11 h-12 text-center text-xl font-bold font-mono text-brand-dark bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/20 focus:outline-none transition" required />
          <input type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" class="otp-input w-11 h-12 text-center text-xl font-bold font-mono text-brand-dark bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/20 focus:outline-none transition" required />
          <input type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" class="otp-input w-11 h-12 text-center text-xl font-bold font-mono text-brand-dark bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/20 focus:outline-none transition" required />
        </div>

        <div id="otpAlert" class="hidden text-xs text-center p-3 rounded-lg border"></div>

        <button 
          type="submit" 
          id="btnVerifyOtp"
          class="w-full py-3 px-4 bg-brand-medium hover:bg-opacity-90 text-white font-bold rounded-xl text-sm transition shadow-sm focus:outline-none cursor-pointer flex items-center justify-center gap-2"
        >
          <span>Verify & Complete Sign In</span>
        </button>

        <div class="text-center pt-2">
          <p class="text-xs text-slate-500">
            Didn't receive the code? 
            <button type="button" id="btnResendOtp" onclick="handleResendOTP()" class="font-bold text-brand-dark hover:underline cursor-pointer focus:outline-none">
              Resend Code
            </button>
            <span id="resendTimerText" class="text-slate-400 font-semibold hidden ml-1"></span>
          </p>
        </div>

      </form>

    </div>
  </div>

  <!-- ============================================================
       Forgot Password Modal  (3-step: identifier → OTP → new password)
       ============================================================ -->
  <div id="forgotModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs transition-all duration-300 opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6 sm:p-8 transform scale-95 transition-all duration-300 border border-slate-100 relative">

      <!-- Close button -->
      <button type="button" onclick="closeForgotModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition cursor-pointer">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>

      <!-- ── STEP 1: Enter identifier ────────────────────────── -->
      <div id="fpStep1">
        <div class="text-center space-y-3 mb-6">
          <div class="h-14 w-14 rounded-2xl bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark mx-auto shadow-xs">
            <i class="fa-solid fa-lock-open text-2xl"></i>
          </div>
          <h3 class="text-xl font-black text-slate-800 tracking-tight">Forgot Password?</h3>
          <p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed">
            Enter your LGU Employee ID or registered email address and we'll send a reset code.
          </p>
        </div>

        <form id="fpForm1" onsubmit="handleForgotStep1(event)" class="space-y-5">
          <div class="space-y-1.5">
            <label for="fpIdentifier" class="text-xs font-semibold text-gray-500">Employee ID or Email</label>
            <div class="relative flex items-center">
              <span class="absolute left-4 text-gray-400"><i class="fa-solid fa-user-tie text-sm"></i></span>
              <input
                type="text"
                id="fpIdentifier"
                placeholder="e.g. SADM-2026-001 or email"
                required
                autocomplete="username"
                class="w-full pl-11 pr-4 py-3 bg-white border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-brand-medium focus:ring-1 focus:ring-brand-medium transition"
              />
            </div>
          </div>

          <div id="fpAlert1" class="hidden text-xs text-center p-3 rounded-lg border"></div>

          <button
            type="submit"
            id="fpBtn1"
            class="w-full py-3 px-4 bg-brand-medium hover:bg-opacity-90 text-white font-bold rounded-xl text-sm transition shadow-sm focus:outline-none cursor-pointer flex items-center justify-center gap-2"
          >
            <span>Send Reset Code</span>
          </button>

          <p class="text-center text-xs text-slate-400">
            Remember your password?
            <button type="button" onclick="closeForgotModal()" class="font-bold text-brand-dark hover:underline focus:outline-none">Sign in</button>
          </p>
        </form>
      </div>

      <!-- ── STEP 2: Verify OTP ─────────────────────────────── -->
      <div id="fpStep2" class="hidden">
        <div class="text-center space-y-3 mb-6">
          <div class="h-14 w-14 rounded-2xl bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark mx-auto shadow-xs">
            <i class="fa-solid fa-envelope-open-text text-2xl"></i>
          </div>
          <h3 class="text-xl font-black text-slate-800 tracking-tight">Check Your Email</h3>
          <p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed">
            We sent a 6-digit reset code to <strong id="fpMaskedEmail" class="text-brand-dark font-mono">your email</strong>. It expires in 10 minutes.
          </p>
        </div>

        <form id="fpForm2" onsubmit="handleForgotStep2(event)" class="space-y-6">
          <!-- 6 OTP boxes -->
          <div class="flex justify-between items-center gap-2 max-w-xs mx-auto">
            <input type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" class="fp-otp-input w-11 h-12 text-center text-xl font-bold font-mono text-brand-dark bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/20 focus:outline-none transition" required autofocus />
            <input type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" class="fp-otp-input w-11 h-12 text-center text-xl font-bold font-mono text-brand-dark bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/20 focus:outline-none transition" required />
            <input type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" class="fp-otp-input w-11 h-12 text-center text-xl font-bold font-mono text-brand-dark bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/20 focus:outline-none transition" required />
            <input type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" class="fp-otp-input w-11 h-12 text-center text-xl font-bold font-mono text-brand-dark bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/20 focus:outline-none transition" required />
            <input type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" class="fp-otp-input w-11 h-12 text-center text-xl font-bold font-mono text-brand-dark bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/20 focus:outline-none transition" required />
            <input type="text" maxlength="1" pattern="[0-9]*" inputmode="numeric" class="fp-otp-input w-11 h-12 text-center text-xl font-bold font-mono text-brand-dark bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:border-brand-medium focus:ring-2 focus:ring-brand-medium/20 focus:outline-none transition" required />
          </div>

          <div id="fpAlert2" class="hidden text-xs text-center p-3 rounded-lg border"></div>

          <button
            type="submit"
            id="fpBtn2"
            class="w-full py-3 px-4 bg-brand-medium hover:bg-opacity-90 text-white font-bold rounded-xl text-sm transition shadow-sm focus:outline-none cursor-pointer flex items-center justify-center gap-2"
          >
            <span>Verify Code</span>
          </button>

          <div class="text-center pt-1">
            <p class="text-xs text-slate-500">
              Didn't receive the code?
              <button type="button" id="fpResendBtn" onclick="handleForgotResend()" class="font-bold text-brand-dark hover:underline cursor-pointer focus:outline-none">Resend</button>
              <span id="fpResendTimer" class="text-slate-400 font-semibold hidden ml-1"></span>
            </p>
          </div>
        </form>
      </div>

      <!-- ── STEP 3: New Password ───────────────────────────── -->
      <div id="fpStep3" class="hidden">
        <div class="text-center space-y-3 mb-6">
          <div class="h-14 w-14 rounded-2xl bg-brand-light border border-brand-border flex items-center justify-center text-brand-dark mx-auto shadow-xs">
            <i class="fa-solid fa-key text-2xl"></i>
          </div>
          <h3 class="text-xl font-black text-slate-800 tracking-tight">Set New Password</h3>
          <p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed">
            Choose a strong password. Must be 8+ characters with uppercase, lowercase, number, and special character.
          </p>
        </div>

        <form id="fpForm3" onsubmit="handleForgotStep3(event)" class="space-y-5">
          <!-- New password -->
          <div class="space-y-1.5">
            <label for="fpNewPass" class="text-xs font-semibold text-gray-500">New Password</label>
            <div class="relative flex items-center">
              <span class="absolute left-4 text-gray-400"><i class="fa-solid fa-lock text-sm"></i></span>
              <input
                type="password"
                id="fpNewPass"
                placeholder="New password"
                required
                autocomplete="new-password"
                class="w-full pl-11 pr-11 py-3 bg-white border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-brand-medium focus:ring-1 focus:ring-brand-medium transition"
              />
              <button type="button" onclick="toggleFpPass('fpNewPass','fpNewPassIcon')" class="absolute right-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                <i id="fpNewPassIcon" class="fa-solid fa-eye-slash text-sm"></i>
              </button>
            </div>
          </div>

          <!-- Confirm password -->
          <div class="space-y-1.5">
            <label for="fpConfirmPass" class="text-xs font-semibold text-gray-500">Confirm Password</label>
            <div class="relative flex items-center">
              <span class="absolute left-4 text-gray-400"><i class="fa-solid fa-lock text-sm"></i></span>
              <input
                type="password"
                id="fpConfirmPass"
                placeholder="Repeat new password"
                required
                autocomplete="new-password"
                class="w-full pl-11 pr-11 py-3 bg-white border border-gray-300 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-brand-medium focus:ring-1 focus:ring-brand-medium transition"
              />
              <button type="button" onclick="toggleFpPass('fpConfirmPass','fpConfirmPassIcon')" class="absolute right-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                <i id="fpConfirmPassIcon" class="fa-solid fa-eye-slash text-sm"></i>
              </button>
            </div>
          </div>

          <!-- Password strength bar -->
          <div class="space-y-1">
            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
              <div id="fpStrengthBar" class="h-full rounded-full transition-all duration-300 w-0"></div>
            </div>
            <p id="fpStrengthLabel" class="text-[10px] text-slate-400 text-right"></p>
          </div>

          <div id="fpAlert3" class="hidden text-xs text-center p-3 rounded-lg border"></div>

          <button
            type="submit"
            id="fpBtn3"
            class="w-full py-3 px-4 bg-brand-medium hover:bg-opacity-90 text-white font-bold rounded-xl text-sm transition shadow-sm focus:outline-none cursor-pointer flex items-center justify-center gap-2"
          >
            <span>Reset Password</span>
          </button>
        </form>
      </div>

      <!-- ── STEP 4: Success ───────────────────────────────── -->
      <div id="fpStep4" class="hidden text-center space-y-5 py-4">
        <div class="h-16 w-16 rounded-full bg-green-50 border border-green-200 flex items-center justify-center text-green-500 mx-auto">
          <i class="fa-solid fa-circle-check text-3xl"></i>
        </div>
        <div class="space-y-2">
          <h3 class="text-xl font-black text-slate-800 tracking-tight">Password Reset!</h3>
          <p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed">
            Your password has been updated successfully. You can now sign in with your new password.
          </p>
        </div>
        <button
          type="button"
          onclick="closeForgotModal()"
          class="w-full py-3 px-4 bg-brand-medium hover:bg-opacity-90 text-white font-bold rounded-xl text-sm transition shadow-sm focus:outline-none cursor-pointer"
        >
          Back to Sign In
        </button>
      </div>

    </div>
  </div>

  <script src="assets/js/login.js"></script>
</body>
</html>