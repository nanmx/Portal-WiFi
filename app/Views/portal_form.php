<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Free Internet PoolCLub</title>
    <meta name="description" content="Free Internet PoolCLub">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Andika:ital,wght@0,400;0,700;1,400;1,700&family=Darker+Grotesque:wght@300..900&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, Andika, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        /* Fondos responsivos */
        body {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        
        /* Fondo para dispositivos wide (pantallas anchas) */
        @media (min-width: 768px) and (orientation: landscape) {
            body {
                background-image: url('<?= base_url('assets/images/wide.png') ?>');
            }
        }
        
        /* Fondo para dispositivos portrait (verticales) */
        @media (max-width: 767px), (orientation: portrait) {
            body {
                background-image: url('<?= base_url('assets/images/portrait.png') ?>');
            }
        }
        
        /* Fallback si ninguna imagen carga */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }
        
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(13, 30, 37, 0.39);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(0px);
        }
        
        /* Logo */
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .portal-logo {
            max-width: 200px;
            height: auto;
            display: inline-block;
        }
        
        h1 {
            color: #dd4814;
            margin-bottom: 20px;
            text-align: center;
        }
        
        /* Estilos para el wizard */
        .form-step {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        .form-step.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-group {
            margin-bottom: 20px;
            font-family: "Andika", sans-serif;
            font-weight: 400;
            font-style: normal;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #f5f5f5;
            font-family: "Andika", sans-serif;
            font-weight: 700;
            font-style: bold;
        }
        
        input[type="text"],
        input[type="email"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        select:focus {
            outline: none;
            border-color: #dd4814;
            box-shadow: 0 0 5px rgba(221,72,20,0.2);
            transform: scale(1.02);
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .error-alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }
        
        .info-message {
            background-color: #e7f3ff;
            color: #004085;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
            font-size: 14px;
        }
        
        /* Botones de navegación */
        .navigation-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .nav-btn {
            background-color: #dd4814;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            flex: 1;
            transition: all 0.3s ease;
        }
        
        .nav-btn:hover:not(:disabled) {
            background-color: #c03c0e;
            transform: translateY(-2px);
        }
        
        .nav-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        .nav-btn.prev-btn {
            background-color: #6c757d;
        }
        
        .nav-btn.prev-btn:hover:not(:disabled) {
            background-color: #5a6268;
        }
        
        .required {
            color: #e74c3c;
        }
        
        /* Estilos para el checkbox y términos */
        .checkbox-group {
            margin: 20px 0;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            margin-top: 3px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin-bottom: 0;
            cursor: pointer;
            font-weight: normal;
        }
        
        details {
            margin-top: 10px;
            margin-left: 28px;
            background-color: #ddd;
            border-radius: 4px;
            padding: 10px;
            border: 1px solid #eee;
        }
        
        summary {
            cursor: pointer;
            color: #000000;
            font-family: "Andika", sans-serif;
            font-weight: 400;
            font-style: italic;
            padding: 5px;
        }
        
        summary:hover {
            text-decoration: underline;
        }
        
        .terms-content {
            margin-top: 10px;
            padding: 10px;
            background-color: white;
            border-radius: 4px;
            font-size: 14px;
            line-height: 1.5;
            font-family: "Andika", sans-serif;
            font-weight: 400;
            font-style: normal;
        }
        
        .terms-content p {
            margin-bottom: 10px;
        }
        
        .warning-text {
            font-size: 13px;
            color: #ffffff;
            margin-top: 5px;
            font-style: italic;
        }
        
        /* Progress indicator */
        .progress-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .progress-step {
            flex: 1;
            text-align: center;
            position: relative;
            color: #ccc;
            font-size: 12px;
        }
        
        .progress-step.active {
            color: #dd4814;
        }
        
        .progress-step.completed {
            color: #28a745;
        }
        
        .progress-step::before {
            content: '';
            display: block;
            width: 30px;
            height: 30px;
            background-color: #ddd;
            border-radius: 50%;
            margin: 0 auto 5px;
            line-height: 30px;
            text-align: center;
        }
        
        .progress-step.active::before {
            background-color: #dd4814;
            content: '●';
            color: white;
            line-height: 30px;
        }
        
        .progress-step.completed::before {
            background-color: #28a745;
            content: '✓';
            color: white;
            line-height: 30px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
            }
            
            .portal-logo {
                max-width: 150px;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                margin: 10px;
                padding: 15px;
            }
            
            .portal-logo {
                max-width: 120px;
            }
            
            h1 {
                font-size: 24px;
            }
        }
        
        .andika-regular {
            font-family: "Andika", sans-serif;
            font-weight: 400;
            font-style: normal;
        }
        
        .andika-bold {
            font-family: "Andika", sans-serif;
            font-weight: 700;
            font-style: normal;
        }
        
        .andika-regular-italic {
            font-family: "Andika", sans-serif;
            font-weight: 400;
            font-style: italic;
        }
        
        .andika-bold-italic {
            font-family: "Andika", sans-serif;
            font-weight: 700;
            font-style: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="andika-bold-italic">Free Internet</h1>
        <div class="logo-container">
            <img src="<?= base_url('assets/images/portalLogo.png') ?>" alt="Portal Logo" class="portal-logo">
        </div>

        <!-- Progress Indicator -->
        <div class="progress-indicator">
            <div class="progress-step" id="step1Indicator">Name</div>
            <div class="progress-step" id="step2Indicator">Email</div>
            <div class="progress-step" id="step3Indicator">Country</div>
            <div class="progress-step" id="step4Indicator">Postal Code</div>
            <div class="progress-step" id="step5Indicator">Terms</div>
        </div>

        <!-- Leyenda informativa -->
        <div class="info-message andika-regular-italic">
            <strong>⚠️ Important:</strong> To ensure uninterrupted access, please enter your real information. 
            Connections with invalid details may be terminated.
        </div>
        
        <?php if(isset($mensaje)): ?>
            <div class="<?= $tipo_mensaje == 'success' ? 'success-message' : 'error-alert' ?>">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>
        
        <?= form_open('form/procesar', ['id' => 'wifiForm']) ?>
            
            <!-- Step 1: Name -->
            <div class="form-step active" id="step1">
                <div class="form-group andika-regular">
                    <?= form_label('Name', 'name') ?>
                    <span class="required">*</span>
                    <?php
                    $name_data = [
                        'name' => 'name',
                        'id' => 'name',
                        'value' => set_value('name'),
                        'placeholder' => 'Enter your name',
                        'class' => 'form-control',
                        'required' => 'required'
                    ];
                    echo form_input($name_data);
                    ?>
                    <div class="error-message" id="nameError">Please enter your full name (minimum 3 characters)</div>
                </div>
            </div>
            
            <!-- Step 2: Email -->
            <div class="form-step" id="step2">
                <div class="form-group">
                    <?= form_label('Email Address', 'email') ?>
                    <span class="required">*</span>
                    <?php
                    $email_data = [
                        'name' => 'email',
                        'id' => 'email',
                        'value' => set_value('email'),
                        'placeholder' => 'example@email.com',
                        'class' => 'form-control',
                        'type' => 'email',
                        'required' => 'required'
                    ];
                    echo form_input($email_data);
                    ?>
                    <div class="error-message" id="emailError">Please enter a valid email address</div>
                </div>
            </div>
            
            <!-- Step 3: Country -->
            <div class="form-step" id="step3">
                <div class="form-group">
                    <?= form_label(' where are you visiting from?', 'pais') ?>
                    <span class="required">*</span>
                    <?php
                    $pais_options = [
                        '' => 'Choose your country.',
                        'MX' => 'México',
                        'USA' => 'United States of America (USA)',
                        'Canada' => 'Canada',
                        'Other' => 'Other'
                    ];
                    echo form_dropdown('pais', $pais_options, set_value('pais'), ['id' => 'pais', 'class' => 'form-control', 'required' => 'required']);
                    ?>
                    <div class="error-message" id="paisError">We’d love to know — where are you visiting from?</div>
                </div>
            </div>
            
            <!-- Step 4: Postal Code -->
            <div class="form-step" id="step4">
                <div class="form-group">
                    <?= form_label('Postal Code', 'codigo_postal') ?>
                    <span class="required">*</span>
                    <?php
                    $cp_data = [
                        'name' => 'codigo_postal',
                        'id' => 'codigo_postal',
                        'value' => set_value('codigo_postal'),
                        'placeholder' => 'Enter your postal code',
                        'class' => 'form-control',
                        'required' => 'required'
                    ];
                    echo form_input($cp_data);
                    ?>
                    <div class="error-message" id="cpError">Please enter a valid postal code (minimum 3 characters)</div>
                </div>
            </div>
            
            <!-- Step 5: Terms and Conditions -->
            <div class="form-step" id="step5">
                <!-- Checkbox de términos y condiciones -->
                <div class="checkbox-group">
                    <input type="checkbox" name="terms_agreed" id="terms_agreed" value="1">
                    <label for="terms_agreed">
                        I agree to the <strong>Terms and Conditions – Marketing Communications Consent</strong>
                    </label>
                </div>
                
                <!-- Details para mostrar los términos -->
                <details>
                    <summary>📋 View Terms and Conditions – Marketing Communications Consent</summary>
                    <div class="terms-content andika-regular">
                        <p><strong>Terms and Conditions – Marketing Communications Consent</strong></p>
                        <p>By accessing and using this network, you agree to provide accurate and truthful information. You also consent to receive promotional emails, special offers, and marketing communications from us.</p>
                        <p>We respect your privacy and will handle your personal information in accordance with applicable data protection laws. Your information will not be sold or shared with third parties without your consent, except as required by law.</p>
                        <p>You may opt out of receiving marketing communications at any time by following the unsubscribe link included in our emails.</p>
                        <p><strong>Providing false or incomplete information may result in restricted or terminated access to the network.</strong></p>
                    </div>
                </details>
                
                <div class="warning-text">
                    By checking the box above, you confirm that you have read and agree to our Terms and Conditions.
                </div>
                <div class="error-message" id="termsError">Please accept the Terms and Conditions to continue</div>
            </div>
            
            <!-- Campos ocultos con datos GET -->
            <input type="hidden" name="clientMac" value="<?= htmlspecialchars($_GET['clientMac'] ?? '') ?>">
            <input type="hidden" name="apMac" value="<?= htmlspecialchars($_GET['apMac'] ?? '') ?>">
            <input type="hidden" name="ssidName" value="<?= htmlspecialchars($_GET['ssidName'] ?? '') ?>">
            <input type="hidden" name="radioId" value="<?= htmlspecialchars($_GET['radioId'] ?? '') ?>">
            <input type="hidden" name="site" value="<?= htmlspecialchars($_GET['site'] ?? '') ?>">
            <input type="hidden" name="redirectUrl" value="<?= htmlspecialchars($_GET['redirectUrl'] ?? '') ?>">
            
            <!-- Botones de navegación -->
            <div class="navigation-buttons">
                <button type="button" class="nav-btn prev-btn" id="prevBtn" style="display: none;">← Previous</button>
                <button type="button" class="nav-btn" id="nextBtn">Next →</button>
                <?= form_submit('submit', 'Get Internet Access!', ['id' => 'submitBtn', 'style' => 'display: none;']) ?>
            </div>
            
        <?= form_close() ?>
    </div>
    
    <script>
        let currentStep = 1;
        const totalSteps = 5;
        
        const stepElements = {
            1: document.getElementById('step1'),
            2: document.getElementById('step2'),
            3: document.getElementById('step3'),
            4: document.getElementById('step4'),
            5: document.getElementById('step5')
        };
        
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const submitBtn = document.getElementById('submitBtn');
        
        // Función para actualizar la visibilidad de los steps
        function updateSteps() {
            // Ocultar todos los steps
            for (let i = 1; i <= totalSteps; i++) {
                stepElements[i].classList.remove('active');
            }
            // Mostrar el step actual
            stepElements[currentStep].classList.add('active');
            
            // Actualizar indicadores de progreso
            for (let i = 1; i <= totalSteps; i++) {
                const indicator = document.getElementById(`step${i}Indicator`);
                if (i < currentStep) {
                    indicator.classList.add('completed');
                    indicator.classList.remove('active');
                } else if (i === currentStep) {
                    indicator.classList.add('active');
                    indicator.classList.remove('completed');
                } else {
                    indicator.classList.remove('active', 'completed');
                }
            }
            
            // Mostrar/Ocultar botones
            if (currentStep === totalSteps) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'block';
            } else {
                nextBtn.style.display = 'block';
                submitBtn.style.display = 'none';
            }
            
            if (currentStep === 1) {
                prevBtn.style.display = 'none';
            } else {
                prevBtn.style.display = 'block';
            }
            
            // Enfocar el primer campo del step actual
            setTimeout(() => {
                const activeStep = stepElements[currentStep];
                const firstInput = activeStep.querySelector('input, select');
                if (firstInput) {
                    firstInput.focus();
                }
            }, 100);
        }
        
        // Validación por paso
        function validateStep(step) {
            let isValid = true;
            
            // Limpiar errores anteriores
            document.querySelectorAll('.error-message').forEach(err => {
                err.classList.remove('show');
            });
            
            switch(step) {
                case 1:
                    const name = document.getElementById('name').value.trim();
                    if (name.length < 3) {
                        document.getElementById('nameError').classList.add('show');
                        isValid = false;
                    }
                    break;
                    
                case 2:
                    const email = document.getElementById('email').value.trim();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        document.getElementById('emailError').classList.add('show');
                        isValid = false;
                    }
                    break;
                    
                case 3:
                    const pais = document.getElementById('pais').value;
                    if (!pais) {
                        document.getElementById('paisError').classList.add('show');
                        isValid = false;
                    }
                    break;
                    
                case 4:
                    const cp = document.getElementById('codigo_postal').value.trim();
                    if (cp.length < 3) {
                        document.getElementById('cpError').classList.add('show');
                        isValid = false;
                    }
                    break;
                    
                case 5:
                    const terms = document.getElementById('terms_agreed').checked;
                    if (!terms) {
                        document.getElementById('termsError').classList.add('show');
                        isValid = false;
                    }
                    break;
            }
            
            return isValid;
        }
        
        // Evento para el botón Siguiente
        nextBtn.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateSteps();
                }
            } else {
                // Mostrar mensaje de error general
                const errorMsg = stepElements[currentStep].querySelector('.error-message');
                if (errorMsg && !errorMsg.classList.contains('show')) {
                    errorMsg.classList.add('show');
                }
            }
        });
        
        // Evento para el botón Anterior
        prevBtn.addEventListener('click', function() {
            if (currentStep > 1) {
                currentStep--;
                updateSteps();
            }
        });
        
        // Validación en tiempo real mientras el usuario escribe
        document.getElementById('name').addEventListener('input', function() {
            if (this.value.trim().length >= 3) {
                document.getElementById('nameError').classList.remove('show');
            }
        });
        
        document.getElementById('email').addEventListener('input', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (emailRegex.test(this.value.trim())) {
                document.getElementById('emailError').classList.remove('show');
            }
        });
        
        document.getElementById('pais').addEventListener('change', function() {
            if (this.value) {
                document.getElementById('paisError').classList.remove('show');
            }
        });
        
        document.getElementById('codigo_postal').addEventListener('input', function() {
            if (this.value.trim().length >= 3) {
                document.getElementById('cpError').classList.remove('show');
            }
        });
        
        document.getElementById('terms_agreed').addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('termsError').classList.remove('show');
            }
        });
        
        // Validación final antes de enviar
        const finalForm = document.getElementById('wifiForm');
        finalForm.addEventListener('submit', function(e) {
            // Validar todos los pasos antes de enviar
            let allValid = true;
            for (let i = 1; i <= totalSteps; i++) {
                if (!validateStep(i)) {
                    allValid = false;
                    currentStep = i;
                    updateSteps();
                    break;
                }
            }
            
            if (!allValid) {
                e.preventDefault();
                alert('Please complete all fields correctly before submitting.');
            }
        });
        
        // Inicializar
        updateSteps();
        
        // Presionar Enter para avanzar al siguiente paso
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const activeStep = stepElements[currentStep];
                const isTextInput = activeStep.querySelector('input[type="text"], input[type="email"]');
                const isSelect = activeStep.querySelector('select');
                
                if (isTextInput || isSelect) {
                    e.preventDefault();
                    if (currentStep === totalSteps) {
                        if (validateStep(currentStep)) {
                            finalForm.submit();
                        }
                    } else {
                        if (validateStep(currentStep)) {
                            currentStep++;
                            updateSteps();
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>