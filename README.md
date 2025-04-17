# 📁 Local Brand X - Laravel File System Watcher

This project is a **real-time file system monitoring tool** built using the Laravel framework. It’s designed with a modular and clean architecture that allows you to monitor any directory on your system and perform custom actions automatically based on file system events like creation, modification, or deletion.

The watcher supports handling different types of files such as `.txt`, `.json`, `.jpg`, and `.zip`, and is easily extensible to support more. This tool is particularly useful for:

- Automating repetitive file-based tasks
- Creating developer tools for testing or data ingestion
- Learning and practicing event-driven architecture and clean code principles in Laravel

Each file type has a dedicated **Watcher** class that encapsulates all related behaviors, and a central **Watcher Manager** coordinates which watcher should respond to each file system event.

The solution avoids infinite loops by tracking already-processed content and ensures robustness by restoring deleted files with memes and re-creating missing directories as needed.

> 🧼 This project emphasizes **clean code**, **separation of concerns**, and **ease of future extensibility**.
---

## 🚀 Features

- ✅ Real-time file system monitoring
- 🖼️ JPG optimization
- 📤 JSON processing via HTTP POST
- 📄 TXT file extension with [Bacon Ipsum](https://baconipsum.com/api/?type=meat-and-filler)
- 🗜️ ZIP file extraction
- 🪖 Anti-delete meme replacement with images from [Meme API](https://meme-api.com/gimme)

---

## 🧪 Installation

```bash
git clone https://github.com/ImanR7/file-system-watcher-laravel.git
cd file-system-watcher-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan watch:fs
```

---

## ⚙️ Configuration

Edit `config/fswatcher.php`:

```php
return [

    /*
    |--------------------------------------------------------------------------
    | Directory to Watch
    |--------------------------------------------------------------------------
    |
    | This is the directory that the watcher service will monitor for file
    | changes (created, modified, deleted).
    |
    */
    'watch_path' => storage_path('fswatch'),

    /*
    |--------------------------------------------------------------------------
    | Polling Interval
    |--------------------------------------------------------------------------
    |
    | The interval (in seconds) between each file system check.
    |
    */
    'polling_interval' => 1,

    /*
    |--------------------------------------------------------------------------
    | Logging Enabled
    |--------------------------------------------------------------------------
    |
    | Whether to log each change in the filesystem.
    |
    */
    'log_changes' => true,
];

```

---

## 📁 Project Structure

```
app/
├── Console/
│   └── Commands/
│       └── WatchFileSystem.php     # Artisan command to run the watcher loop
├── Services/
│   └── FileWatcher/
│       ├── WatcherManager.php      # Core manager to dispatch file events to watchers
│       ├── Contracts/
│       │   └── FileWatcherInterface.php  # Interface for all watcher classes
│       └── Watchers/
│           ├── TxtFileWatcher.php  # Handles .txt files
│           ├── JsonFileWatcher.php # Handles .json files
│           ├── JpgFileWatcher.php  # Handles .jpg files
│           ├── ZipFileWatcher.php  # Handles .zip files
│           └── AntiDeleteMemeWatcher.php # Handles deleted files
config/
└── fswatcher.php                # Config file containing watch path and MIME-type map

tests/
└── Feature/
    └── FileWatcherTest.php         # Full-feature test coverage for all watcher types
```

### 🧾 Artisan Command

Start the watcher using the custom artisan command:

```bash
php artisan watch:fs
```

---

## 🔍 Watcher Behaviors

### 1. `.txt` Files
- Appends random text from Bacon Ipsum API.
- Prevents infinite processing by checking for:
  ```
  <!-- GENERATED_BY_TXT_WATCHER -->
  ```

### 2. `.json` Files
- Sends the file content via HTTP POST to:
  ```
  https://fswatcher.requestcatcher.com/
  ```

### 3. `.jpg` Files
- Optimized using [Intervention Image](https://image.intervention.io/).
- Skips re-optimization if already optimized.

### 4. `.zip` Files
- Automatically extracted using `ZipArchive`.

### 5. Deleted Files
- Restored with a random meme image (from Meme API).
- Uses same filename as deleted, but with `.jpg` extension.
- Recreates directory structure if needed.

---

## ✅ Tests

This project includes automated tests covering all the major features of the file system watcher. Tests are located in:

```
tests/Feature/FileWatcherTest.php
```

The tests cover the following functionality:

- **Text File Processing (.txt)**: Verifies that Bacon Ipsum content is appended only once to the text file.
- **JSON File Processing (.json)**: Ensures that a POST request is made to the endpoint with the file's contents.
- **JPG File Optimization (.jpg)**: Confirms the JPG file is optimized (compressed) using Intervention Image.
- **ZIP File Extraction (.zip)**: Checks that files inside a ZIP archive are extracted correctly.
- **Deleted File Meme Replacement**: Validates that deleted files are replaced by a meme image, preserving the original filename (with changed extension).

---

## 🛠️ Dependencies

| Library               | Purpose                     |
|-----------------------|-----------------------------|
| intervention/image    | JPG optimization            |
| Laravel HTTP Client   | Bacon Ipsum & Meme APIs     |

---

## 🧠 Design Considerations

- Clean, modular architecture using the **Strategy Pattern**
- Each file type handled by a separate class
- Easily extendable:
  - Just create a new class implementing `FileWatcherInterface`
  - Register it in the watcher manager

---

## ➕ Adding a New Watcher

1. Create a class in `Watchers/` implementing:

```php
public function supports(SplFileInfo $file, string $event): bool
public function handle(SplFileInfo $file, string $event): void
```

2. Register the new watcher in `app/Services/FileWatcher/WatcherManager.php`.

---

## 📎 Example Folder Structure

```
storage/
└── fswatch/
    ├── test.txt
    ├── image.jpg
    ├── data.json
    ├── archive.zip
```

---

## 👨‍💻 Author

**Iman Rajabi** | Backend Developer

---

## 🧬 Future Improvements

- Daemonize the watcher using Supervisor or schedule
- Add new file handlers for PDF, CSV, etc.
- Logging to database or dedicated log file

---

> Built with ❤️ using clean architecture principles.
