<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Список зарегистрированных пользователей</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .user-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0,0,0,0.15);
        }
        
        .user-avatar {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
            background: #f0f0f0;
        }
        
        .user-nickname {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            text-align: center;
            word-break: break-word;
        }
        
        .no-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e0e0e0;
            color: #999;
            font-size: 0.9em;
        }
        
        .empty-state {
            text-align: center;
            color: white;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .empty-state h2 {
            margin-bottom: 10px;
        }
        
        .registration-form {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .registration-form h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input[type="text"],
        .form-group input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        
        .form-group input[type="text"]:focus,
        .form-group input[type="file"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group input[type="file"] {
            padding: 8px;
            cursor: pointer;
        }
        
        .file-info {
            margin-top: 8px;
            font-size: 0.9em;
            color: #666;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
        }
        
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert.show {
            display: block;
        }
        
        .preview-container {
            margin-top: 15px;
            text-align: center;
        }
        
        .avatar-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            display: none;
        }
        
        .avatar-preview.show {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Список зарегистрированных пользователей</h1>
        
        <div class="registration-form">
            <h2>Регистрация нового пользователя</h2>
            
            <div class="alert alert-success" id="successAlert"></div>
            <div class="alert alert-error" id="errorAlert"></div>
            
            <form id="registrationForm">
                <div class="form-group">
                    <label for="nickname">Nickname *</label>
                    <input type="text" id="nickname" name="nickname" required maxlength="255" placeholder="Введите nickname">
                </div>
                
                <div class="form-group">
                    <label for="avatar">Avatar *</label>
                    <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/jpg,image/png,image/gif" required>
                    <div class="file-info">Разрешены форматы: JPEG, JPG, PNG, GIF. Максимальный размер: 2MB</div>
                    <div class="preview-container">
                        <img id="avatarPreview" class="avatar-preview" alt="Preview">
                    </div>
                </div>
                
                <button type="submit" class="btn-submit" id="submitBtn">Зарегистрировать</button>
            </form>
        </div>
        
        @if(count($users) > 0)
            <div class="users-grid">
                @foreach($users as $user)
                    <div class="user-card">
                        @if($user['avatar_url'])
                            <img src="{{ $user['avatar_url'] }}" alt="{{ $user['nickname'] }}" class="user-avatar">
                        @else
                            <div class="user-avatar no-avatar">Нет аватара</div>
                        @endif
                        <div class="user-nickname">{{ $user['nickname'] }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <h2>Пользователи не найдены</h2>
                <p>Зарегистрированных пользователей пока нет.</p>
            </div>
        @endif
    </div>
    
    <script>
        const form = document.getElementById('registrationForm');
        const nicknameInput = document.getElementById('nickname');
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatarPreview');
        const submitBtn = document.getElementById('submitBtn');
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        
        // Preview avatar before upload
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showError('Размер файла превышает 2MB');
                    avatarInput.value = '';
                    avatarPreview.classList.remove('show');
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showError('Недопустимый тип файла. Разрешены только JPEG, JPG, PNG, GIF');
                    avatarInput.value = '';
                    avatarPreview.classList.remove('show');
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                    avatarPreview.classList.add('show');
                };
                reader.readAsDataURL(file);
            } else {
                avatarPreview.classList.remove('show');
            }
        });
        
        // Handle form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            hideAlerts();
            
            const nickname = nicknameInput.value.trim();
            const avatarFile = avatarInput.files[0];
            
            if (!nickname) {
                showError('Пожалуйста, введите nickname');
                return;
            }
            
            if (!avatarFile) {
                showError('Пожалуйста, выберите файл аватара');
                return;
            }
            
            // Convert file to base64
            const base64Avatar = await fileToBase64(avatarFile);
            
            if (!base64Avatar) {
                showError('Ошибка при обработке файла');
                return;
            }
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Регистрация...';
            
            try {
                const response = await fetch('/api/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nickname: nickname,
                        avatar: base64Avatar
                    })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    showSuccess('Пользователь успешно зарегистрирован!');
                    form.reset();
                    avatarPreview.classList.remove('show');
                    
                    // Reload page after 1 second to show new user
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    const errorMessage = data.message || data.errors ? 
                        (typeof data.errors === 'object' ? 
                            Object.values(data.errors).flat().join(', ') : 
                            data.message) : 
                        'Ошибка при регистрации';
                    showError(errorMessage);
                }
            } catch (error) {
                showError('Ошибка сети: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Зарегистрировать';
            }
        });
        
        function fileToBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        }
        
        function showSuccess(message) {
            successAlert.textContent = message;
            successAlert.classList.add('show');
            errorAlert.classList.remove('show');
        }
        
        function showError(message) {
            errorAlert.textContent = message;
            errorAlert.classList.add('show');
            successAlert.classList.remove('show');
        }
        
        function hideAlerts() {
            successAlert.classList.remove('show');
            errorAlert.classList.remove('show');
        }
    </script>
</body>
</html>

