# Решение проблемы с миграциями

Проблема: отсутствует файл `vendor/autoload.php`

## Решение:

1. **Удалите папку vendor** (опционально, если проблемы продолжаются):
   ```
   rmdir /s vendor
   ```

2. **Переустановите зависимости Composer:**
   ```
   composer install
   ```

3. **Если это не помогло, выполните:**
   ```
   composer update
   composer dump-autoload
   ```

4. **Затем выполните миграции:**
   ```
   php artisan migrate
   ```

## Альтернативный способ:

Если проблемы с Composer продолжаются, попробуйте создать autoload файлы вручную:
```
composer dump-autoload -o
```

После этого снова попробуйте:
```
php artisan migrate
```
