<?php
namespace EKO\DesignSystem;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;


/**
 * `AssetPublisher` copies the design system's static files (`assets/css/`, `assets/js/`, `assets/fonts/`, `assets/icons/` and `assets/addons/`)
 * from the Composer package into a folder of the app that the web server serves, such as `public/design-system`.
 * The files are copied, not linked, so the app also works offline and on Windows.
 *
 * The easiest way is to let Composer publish the files after every install and update.
 * Add this to the app's own composer.json (scripts of dependencies are not run, so it must be the app's file):
 * ```json
 * "extra": {
 *     "eko-design-system": { "public-dir": "public/design-system" }
 * },
 * "scripts": {
 *     "post-install-cmd": ["EKO\\DesignSystem\\AssetPublisher::composerPublish"],
 *     "post-update-cmd": ["EKO\\DesignSystem\\AssetPublisher::composerPublish"]
 * }
 * ```
 * The folder is relative to the app's root. Publish once by hand with `composer run-script post-install-cmd`,
 * or without Composer scripts with `vendor/bin/eko-publish-assets public/design-system`, or from PHP:
 * ```php
 * use EKO\DesignSystem\AssetPublisher;
 *
 * AssetPublisher::publish(__DIR__ . '/public/design-system');
 * ```
 *
 * Then point the components at the folder's URL, once, before any component is rendered:
 * `DesignSystem::setBaseUrl('/design-system');`
 *
 * The publisher owns the folder: on every run it empties it and copies the current files, so icons that were
 * removed in a newer version disappear too. To protect the app's own files, it only does this to a folder that
 * is new, empty, or was created by the publisher (it leaves a `.eko-design-system` marker file).
 * Add the folder to the app's .gitignore, like `vendor/`.
 */
class AssetPublisher {


    /**
     * The folders that are published, relative to the package root. The demo images in assets/images are left out.
     * Every addon's files (assets/addons/) are published, enabled or not, so switching an addon on needs no new publish.
     */
    public const FOLDERS = [
        'assets/css',
        'assets/js',
        'assets/fonts',
        'assets/icons',
        'assets/addons',
    ];


    /** The top-level folders the publisher creates in the destination, and empties before each run. */
    private const OWNED_FOLDERS = [
        'assets',
    ];

    /** The file that marks a folder as created by the publisher. It holds the published version. */
    private const MARKER_FILE = '.eko-design-system';

    /** The Composer package name, used to look up the installed version. */
    private const PACKAGE_NAME = 'eko-instruments/design-system';

    /** The key of the publisher's settings in the `extra` section of the app's composer.json. */
    private const EXTRA_KEY = 'eko-design-system';


    /**
     * Copies the static files into `$destination`, replacing an earlier publish.
     *
     * @param string $destination
     * The folder to publish to (e.g., "public/design-system"). A relative path is relative to the current working directory.
     * It is created if needed. It must be new, empty, or created by an earlier publish.
     *
     * @return int
     * The number of files that were copied.
     *
     * @throws RuntimeException if `$destination` is empty, is a file, is not empty and was not created by the publisher,
     * lies inside the published folders, or a file or folder cannot be created, copied or removed.
     */
    public static function publish($destination) {
        $source = dirname(__DIR__);
        $destination = rtrim(str_replace('\\', '/', (string) $destination), '/');
        if ($destination === '') {
            throw new RuntimeException("No destination folder given to publish the design system's assets to");
        }


        // Make sure the destination is a folder the publisher may empty
        if (is_file($destination)) {
            throw new RuntimeException("Cannot publish to a file: $destination");
        }
        if (is_dir($destination) && !is_file("$destination/" . self::MARKER_FILE) && !self::isEmptyDirectory($destination)) {
            throw new RuntimeException(
                "Cannot publish to $destination: the folder is not empty and was not created by AssetPublisher. "
                . "Use a folder of its own, such as public/design-system."
            );
        }
        self::createDirectory($destination);
        self::checkNotInsideSource($source, $destination);


        // Remove the previous publish, then copy the current files
        foreach (self::OWNED_FOLDERS as $folder) {
            if (is_dir("$destination/$folder")) {
                self::removeDirectory("$destination/$folder");
            }
        }
        $count = 0;
        foreach (self::FOLDERS as $folder) {
            $count += self::copyDirectory("$source/$folder", "$destination/$folder");
        }

        // Mark the folder as the publisher's, so the next run may empty it
        $marker = "Published by " . self::class . "\n"
            . "Version: " . self::installedVersion() . "\n"
            . "Date: " . date('c') . "\n";
        if (file_put_contents("$destination/" . self::MARKER_FILE, $marker) === false) {
            throw new RuntimeException("Cannot write the marker file in: $destination");
        }

        return $count;
    }


    /**
     * Publishes the files from a Composer script, to the folder set in the app's composer.json
     * (`extra.eko-design-system.public-dir`). See the class description for the setup.
     * Without that setting, it prints a warning and publishes nothing.
     *
     * @param \Composer\Script\Event $event
     * The event Composer passes to the script.
     *
     * @throws RuntimeException if publishing fails. Composer then reports the error and stops.
     */
    public static function composerPublish($event) {
        $io = $event->getIO();
        $extra = $event->getComposer()->getPackage()->getExtra();
        $publicDir = $extra[self::EXTRA_KEY]['public-dir'] ?? null;

        if (!is_string($publicDir) || $publicDir === '') {
            $io->writeError(
                "<warning>EKO design system: no assets published. Set \"extra\": {\"" . self::EXTRA_KEY
                . "\": {\"public-dir\": \"public/design-system\"}} in composer.json.</warning>"
            );
            return;
        }

        $count = self::publish($publicDir);
        $io->write("<info>EKO design system:</info> published $count files to $publicDir");
    }


    /**
     * Copies a folder and everything in it.
     *
     * @param string $source
     * The folder to copy.
     *
     * @param string $destination
     * The folder to copy to. It is created if needed.
     *
     * @return int
     * The number of files that were copied.
     *
     * @throws RuntimeException if `$source` does not exist, or a folder or file cannot be created or copied.
     */
    private static function copyDirectory($source, $destination) {
        if (!is_dir($source)) {
            throw new RuntimeException("The design system folder is missing: $source");
        }
        self::createDirectory($destination);

        $count = 0;
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($items as $item) {
            $target = "$destination/" . str_replace('\\', '/', $items->getSubPathname());
            if ($item->isDir()) {
                self::createDirectory($target);
            } else {
                if (!copy($item->getPathname(), $target)) {
                    throw new RuntimeException("Cannot copy file: $target");
                }
                $count++;
            }
        }
        return $count;
    }


    /**
     * Removes a folder and everything in it.
     *
     * @param string $directory
     * The folder to remove.
     *
     * @throws RuntimeException if a file or folder cannot be removed.
     */
    private static function removeDirectory($directory) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $removed = $item->isDir() && !$item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            if (!$removed) {
                throw new RuntimeException("Cannot remove: " . $item->getPathname());
            }
        }
        if (!rmdir($directory)) {
            throw new RuntimeException("Cannot remove directory: $directory");
        }
    }


    /**
     * Creates a folder, including missing parent folders, unless it already exists.
     *
     * @param string $directory
     * The folder to create.
     *
     * @throws RuntimeException if the folder cannot be created.
     */
    private static function createDirectory($directory) {
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException("Cannot create directory: $directory");
        }
    }


    /**
     * Checks that the destination does not lie inside one of the published folders,
     * which would make the publisher copy a folder into itself.
     *
     * @param string $source
     * The package root.
     *
     * @param string $destination
     * The destination folder. It must exist.
     *
     * @throws RuntimeException if `$destination` lies inside a published folder.
     */
    private static function checkNotInsideSource($source, $destination) {
        $target = self::normalisePath(realpath($destination));
        foreach (self::OWNED_FOLDERS as $folder) {
            $published = realpath("$source/$folder");
            if ($published === false) {
                continue;
            }
            $published = self::normalisePath($published);
            if ($target === $published || str_starts_with($target, "$published/")) {
                throw new RuntimeException("Cannot publish into the design system's own $folder folder: $destination");
            }
        }
    }


    /**
     * Checks whether a folder is empty.
     *
     * @param string $directory
     * The folder to check. It must exist.
     *
     * @return bool
     * `true` if the folder holds no files or folders.
     */
    private static function isEmptyDirectory($directory) {
        return !(new FilesystemIterator($directory))->valid();
    }


    /**
     * Makes a path comparable: forward slashes, and lowercase on Windows, where paths are case-insensitive.
     *
     * @param string $path
     * The path.
     *
     * @return string
     * The normalised path.
     */
    private static function normalisePath($path) {
        $path = str_replace('\\', '/', (string) $path);
        return DIRECTORY_SEPARATOR === '\\' ? strtolower($path) : $path;
    }


    /**
     * Looks up the installed version of the design system package, for the marker file.
     *
     * @return string
     * The version (e.g., "v1.2.0"), or "unknown" when it is not installed through Composer.
     */
    private static function installedVersion() {
        $versions = '\Composer\InstalledVersions';
        if (class_exists($versions) && $versions::isInstalled(self::PACKAGE_NAME)) {
            return (string) $versions::getPrettyVersion(self::PACKAGE_NAME);
        }
        return 'unknown';
    }
}
