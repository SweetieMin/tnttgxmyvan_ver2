# Site Settings Guide

File này hướng dẫn cách thêm một setting mới vào hệ thống trong tương lai.

## 1. Thêm record vào nơi update setting

Nếu setting đó được quản lý ở màn hình admin hiện có, thêm key vào đúng Livewire component tương ứng.

Ví dụ:
- theme: [ThemeSettings.php](/Users/smyth/Herd/tnttgxmyvan-ver2/app/Livewire/Admin/Settings/Site/ThemeSettings.php)
- general/branding/social: [GeneralSettings.php](/Users/smyth/Herd/tnttgxmyvan-ver2/app/Livewire/Admin/Settings/Site/GeneralSettings.php)

Bạn cần thêm:
- chỗ đọc giá trị ban đầu
- chỗ validate nếu cần
- chỗ `upsertSetting()`
- metadata trong `settingDefinitions()` nếu component đó có dùng

Ví dụ record mới:

```php
'branding.login_image' => [
    'group' => 'branding',
    'type' => 'image',
    'label' => 'Login image',
    'description' => 'Hình minh họa dùng ở màn hình đăng nhập.',
    'is_public' => true,
    'is_encrypted' => false,
    'autoload' => true,
    'sort_order' => 95,
],
```

## 2. Nếu setting cần load toàn hệ thống

Thêm default vào [SiteSettings.php](/Users/smyth/Herd/tnttgxmyvan-ver2/app/Foundation/SiteSettings.php) trong mảng `$defaults`.

Ví dụ:

```php
'branding.login_image' => '',
```

`autoload = true` nghĩa là record này sẽ được service load và cache tự động.

## 3. Gắn setting vào đúng nhóm

Chọn class nhóm phù hợp trong thư mục [app/Foundation/SiteSettings](/Users/smyth/Herd/tnttgxmyvan-ver2/app/Foundation/SiteSettings):

- theme: [ThemeSettings.php](/Users/smyth/Herd/tnttgxmyvan-ver2/app/Foundation/SiteSettings/ThemeSettings.php)
- branding: [BrandingSettings.php](/Users/smyth/Herd/tnttgxmyvan-ver2/app/Foundation/SiteSettings/BrandingSettings.php)
- general: [GeneralSettings.php](/Users/smyth/Herd/tnttgxmyvan-ver2/app/Foundation/SiteSettings/GeneralSettings.php)
- social: [SocialSettings.php](/Users/smyth/Herd/tnttgxmyvan-ver2/app/Foundation/SiteSettings/SocialSettings.php)

Ví dụ thêm method cho branding:

```php
public function loginImage(): ?string
{
    return $this->settings['branding.login_image'] ?: null;
}
```

Và nhớ thêm vào `toArray()` nếu nhóm đó đang dùng.

## 4. Nếu cần dùng trực tiếp từ service tổng

Dùng qua [SiteSettings.php](/Users/smyth/Herd/tnttgxmyvan-ver2/app/Foundation/SiteSettings.php):

```php
app(\App\Foundation\SiteSettings::class)->branding()->loginImage();
```

Hoặc nếu vẫn cần đọc theo key:

```php
app(\App\Foundation\SiteSettings::class)->get('branding.login_image');
```

## 5. Nếu cần share ra tất cả view

Thêm key vào mảng `$sharedKeys` trong [SiteSettings.php](/Users/smyth/Herd/tnttgxmyvan-ver2/app/Foundation/SiteSettings.php).

Ví dụ:

```php
'siteLoginImage' => 'branding.login_image',
```

Sau đó trong Blade có thể dùng:

```blade
{{ $siteLoginImage }}
```

Không cần sửa lại [AppServiceProvider.php](/Users/smyth/Herd/tnttgxmyvan-ver2/app/Providers/AppServiceProvider.php) vì provider đã gọi `shared()` tự động.

## 6. Cache

Không cần clear cache thủ công.

Model [Setting.php](/Users/smyth/Herd/tnttgxmyvan-ver2/app/Models/Setting.php) đã tự `forget()` cache mỗi khi setting được lưu hoặc xóa.

## 7. Test tối thiểu nên thêm

Update [SiteSettingsServiceTest.php](/Users/smyth/Herd/tnttgxmyvan-ver2/tests/Feature/SiteSettingsServiceTest.php) khi:
- thêm default mới
- thêm method mới trong group class
- thêm biến shared mới cho view

Ví dụ nên kiểm tra:
- service trả default đúng
- service đọc record autoload đúng
- view render ra biến shared đúng nếu setting đó dùng cho giao diện

## 8. Checklist ngắn

1. Thêm/ghi record trong Livewire settings component.
2. Thêm default vào `SiteSettings::$defaults`.
3. Thêm method vào đúng group class.
4. Nếu cần dùng trong mọi view, thêm vào `SiteSettings::$sharedKeys`.
5. Thêm hoặc cập nhật test.
