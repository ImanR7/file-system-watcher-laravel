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

## 🧠 Development Challenges & Design Decisions

### Challenges Encountered

During the development of the Laravel File System Watcher, several challenges arose:

- **Preventing infinite loops**: When modifying or restoring files, it was essential to prevent the watcher from reacting to its own changes.
- **Ensuring directory existence**: When restoring deleted files or extracting ZIP archives, the application had to ensure the target directory existed — including support for nested directories.
- **Preserving file naming patterns**: Restored or replaced files needed to maintain the original filenames or structure (e.g., maintaining the `.zip` filename when replacing with a `.jpg` meme).
- **Handling invalid JSON content**: Malformed JSON files had to be detected without breaking the entire command execution.
- **Test automation without real fixtures**: Creating file-based test cases required handling temporary directories and synthetic file creation without relying on external assets.



### Initial Ideas and Unimplemented Approaches

Some approaches were considered but ultimately not implemented:

- **Using a queue system**: Instead of processing file events synchronously, a queued approach could decouple watchers from processing, but it was omitted to keep the system lightweight.
- **Using file hashes**: One idea to prevent re-processing identical files (especially images) was using file hashes, but instead a comparison of file contents before and after modification (using `md5_file`) was used for simplicity.



### Solutions Implemented

The following strategies were used to address the identified challenges:

- **Recursive directory creation**: Used `mkdir($path, 0777, true)` to safely handle restoration in nested paths.
- **Custom exceptions for JSON errors**: Introduced `InvalidJsonException` and `WatcherErrorException` to isolate errors and prevent the entire command from failing.
- **Try-catch handling in each watcher**: Ensures each file event is isolated and errors do not stop the entire watch process.
- **Synthetic file generation in tests**: All tests dynamically generate their own files and directories, ensuring they are self-contained and environment-independent.



### Extensibility for Future Enhancements

The architecture is designed with modularity and extensibility in mind:

- **Dedicated Watcher classes**: Each file extension is handled by its own class, following the `handle(SplFileInfo $file, string $event)` contract.
- **Watcher Manager**: Coordinates all watchers and routes events based on file extension and supported events.
- **Enums for supported extensions and events**: `SupportedEvents` and `SupportedExtensions` provide centralized management for supported types, making it easier to extend in the future.
- **Configuration-based setup**: Developers can configure which directories to watch and which events/extensions are enabled without changing the core logic.
- **Exception handling ready for external reporting**: The system can be easily extended to report to external services like Sentry or Slack in case of runtime exceptions.

This design ensures that adding a new watcher (e.g., for `.csv`, `.xml`, or `.log` files) is as simple as creating a new class and registering it with the manager.

---

## 🧪 Installation

```bash
git clone https://github.com/ImanR7/file-system-watcher-laravel.git
cd file-system-watcher-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan test
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
├── Enums/
│       ├── SupportedEvents.php  # Supported events Enum
│       └── SupportedExtensions.php # Supported extensions Enum
├── Exceptions/
│       ├── InvalidJsonException.php  # Invalid JSON file exception
│       └── WatcherErrorException.php # Watcher error exception
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

## 🧩 Enums

To enforce consistency and avoid hard-coded strings throughout the codebase, two PHP 8.1+ enums have been defined: `SupportedEvents` and `SupportedExtensions`.

### `App\Enums\SupportedEvents`

This enum defines the supported file system events the watcher reacts to:

| Enum Case | Value     | Description                       |
|-----------|-----------|-----------------------------------|
| `CREATED` | `created` | Triggered when a file is created  |
| `MODIFIED`| `modified`| Triggered when a file is modified |
| `DELETED` | `deleted` | Triggered when a file is deleted  |


### `App\Enums\SupportedExtensions`

This enum defines the supported file types that can be monitored and handled:

| Enum Case | Value     | Description              |
|-----------|-----------|--------------------------|
| `JPG`     | `jpg`     | JPEG image format        |
| `JPEG`    | `jpeg`    | Alternative image format |
| `JSON`    | `json`    | JSON data file           |
| `ZIP`     | `zip`     | Zip archive file         |
| `TXT`     | `txt`     | Plain text file          |

---


## ⚠️ Custom Exceptions

To improve error handling and maintain clean, readable code, this project defines two custom exception classes located in the `App\Exceptions` namespace. These exceptions allow watcher classes to throw domain-specific errors instead of relying on inline logging, making the system easier to debug, maintain, and test.

### `InvalidJsonException`

```php
class InvalidJsonException extends Exception
{
    public function __construct(string $filePath)
    {
        parent::__construct("Invalid JSON in file: {$filePath}");
    }
}
```

```php
class WatcherErrorException extends Exception
{
    public function __construct(string $filePath, string $message)
    {
        parent::__construct("{$filePath} Watcher error: {$message}");
    }
}
```


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

1. Create a class in `app/Services/FileWatcher/Watchers/` implementing:

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
