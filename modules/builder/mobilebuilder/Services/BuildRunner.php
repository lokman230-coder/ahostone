<?php
/**
 * MobileBuilder Build Runner
 * Ahost One v25.0.0 RC20
 * 
 * Bu script arka planda Gradle build çalıştırır.
 * Komut satırından: php BuildRunner.php <build_id> <user_id> <project_json> <build_type>
 */

require_once dirname(__DIR__, 4) . '/bootstrap.php';

// Argümanları al
if ($argc < 5) {
    die("Usage: php BuildRunner.php <build_id> <user_id> <project_json> <build_type>\n");
}

$buildId = (int)$argv[1];
$userId = (int)$argv[2];
$projectData = json_decode($argv[3], true);
$buildType = $argv[4];

// Build dizini
$buildDir = defined('AHOST_BUILD_DIR') ? AHOST_BUILD_DIR : dirname(__DIR__, 4) . '/storage/builds/mobile-builder';
$projectDir = $buildDir . '/temp/project_' . $buildId . '_' . time();

// Log dosyası
$logFile = $buildDir . '/logs/' . $buildId . '.log';

function log_msg($msg) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $line = "[{$timestamp}] {$msg}\n";
    echo $line;
    file_put_contents($logFile, $line, FILE_APPEND);
}

function update_status($status) {
    global $db, $buildId;
    $stmt = $db->prepare("UPDATE module_mobilebuilder_builds SET status = ? WHERE id = ?");
    $stmt->execute([$status, $buildId]);
}

// Başla
log_msg("=== Build Runner Started ===");
log_msg("Build ID: {$buildId}");
log_msg("User ID: {$userId}");
log_msg("Build Type: {$buildType}");
log_msg("Project: " . ($projectData['name'] ?? 'Unknown'));

try {
    // 1. Sistem gereksinimlerini kontrol et
    log_msg("[1/6] Sistem gereksinimleri kontrol ediliyor...");
    
    $javaVersion = trim(shell_exec('java -version 2>&1 | head -1'));
    if (empty($javaVersion)) {
        throw new Exception("Java JDK bulunamadı!");
    }
    log_msg("Java: " . $javaVersion);
    
    $gradleVersion = trim(shell_exec('gradle --version 2>&1 | head -1'));
    if (empty($gradleVersion)) {
        throw new Exception("Gradle bulunamadı!");
    }
    log_msg("Gradle: " . $gradleVersion);
    
    $androidHome = getenv('ANDROID_HOME') ?: '/opt/android-sdk';
    if (!is_dir($androidHome)) {
        throw new Exception("Android SDK bulunamadı: {$androidHome}");
    }
    log_msg("Android SDK: {$androidHome}");
    
    // 2. Geçici dizin oluştur
    log_msg("[2/6] Proje dizini oluşturuluyor...");
    if (!mkdir($projectDir, 0755, true)) {
        throw new Exception("Proje dizini oluşturulamadı: {$projectDir}");
    }
    log_msg("Proje dizini: {$projectDir}");
    
    // 3. Android proje şablonunu kopyala veya oluştur
    log_msg("[3/6] Android proje oluşturuluyor...");
    
    $templateDir = $buildDir . '/android-template';
    if (is_dir($templateDir)) {
        // Şablonu kopyala
        exec("cp -r {$templateDir}/* {$projectDir}/");
        log_msg("Şablon kopyalandı: {$templateDir}");
    } else {
        // Minimal Android proje oluştur
        create_minimal_android_project($projectDir, $projectData);
        log_msg("Minimal Android proje oluşturuldu");
    }
    
    // 4. build.gradle ve settings.gradle güncelle
    log_msg("[4/6] Build yapılandırması güncelleniyor...");
    
    $packageName = $projectData['package_name'] ?? 'com.example.app';
    $appName = $projectData['name'] ?? 'Mobil Uygulama';
    
    // settings.gradle
    $settingsContent = "pluginManagement {\n    repositories {\n        google()\n        mavenCentral()\n        gradlePluginPortal()\n    }\n}\ndependencyResolutionManagement {\n    repositoriesMode.set(RepositoriesMode.FAIL_ON_PROJECT_REPOS)\n    repositories {\n        google()\n        mavenCentral()\n    }\n}\nrootProject.name = \"" . addslashes($appName) . "\"\ninclude(\":app\")\n";
    file_put_contents($projectDir . '/settings.gradle', $settingsContent);
    log_msg("settings.gradle güncellendi");
    
    // app/build.gradle
    $buildGradle = file_get_contents($projectDir . '/app/build.gradle');
    $buildGradle = str_replace('com.example.app', $packageName, $buildGradle);
    $buildGradle = str_replace('applicationId "com.example.app"', 'applicationId "' . $packageName . '"', $buildGradle);
    $buildGradle = str_replace('namespace "com.example.app"', 'namespace "' . $packageName . '"', $buildGradle);
    file_put_contents($projectDir . '/app/build.gradle', $buildGradle);
    log_msg("app/build.gradle güncellendi");
    
    // gradle.properties
    $propsContent = "org.gradle.jvmargs=-Xmx2048m -Dfile.encoding=UTF-8\nandroid.useAndroidX=true\nandroid.enableJetifier=true\nandroid.nonTransitiveRClass=true\n";
    file_put_contents($projectDir . '/gradle.properties', $propsContent);
    log_msg("gradle.properties güncellendi");
    
    // 5. Gradle build çalıştır
    log_msg("[5/6] Gradle build başlatılıyor...");
    update_status('building');
    
    chdir($projectDir);
    
    if ($buildType === 'aab') {
        // AAB (Android App Bundle) build
        log_msg("Komut: ./gradlew bundleRelease");
        
        $output = [];
        $returnCode = 0;
        exec('./gradlew bundleRelease 2>&1', $output, $returnCode);
        
        $outputStr = implode("\n", $output);
        log_msg("Gradle output:\n" . substr($outputStr, 0, 2000));
        
        if ($returnCode !== 0) {
            throw new Exception("Gradle bundleRelease başarısız! Return code: {$returnCode}");
        }
        
        // AAB dosyasını bul
        $aabPath = $projectDir . '/app/build/outputs/bundle/release/app-release.aab';
        if (!file_exists($aabPath)) {
            // Alternatif yol
            $aabFiles = glob($projectDir . '/app/build/outputs/bundle/**/*.aab');
            if (!empty($aabFiles)) {
                $aabPath = end($aabFiles);
            }
        }
        
        if (file_exists($aabPath)) {
            $finalPath = $buildDir . '/aab/build_' . $buildId . '.aab';
            copy($aabPath, $finalPath);
            log_msg("AAB oluşturuldu: {$finalPath}");
            
            // DB güncelle
            $stmt = $db->prepare("UPDATE module_mobilebuilder_builds SET status = 'completed', download_path = ?, file_size = ? WHERE id = ?");
            $stmt->execute([$finalPath, filesize($finalPath), $buildId]);
            log_msg("Veritabanı güncellendi");
        } else {
            throw new Exception("AAB dosyası bulunamadı!");
        }
        
    } else {
        // APK build
        log_msg("Komut: ./gradlew assembleRelease");
        
        $output = [];
        $returnCode = 0;
        exec('./gradlew assembleRelease 2>&1', $output, $returnCode);
        
        $outputStr = implode("\n", $output);
        log_msg("Gradle output:\n" . substr($outputStr, 0, 2000));
        
        if ($returnCode !== 0) {
            throw new Exception("Gradle assembleRelease başarısız! Return code: {$returnCode}");
        }
        
        // APK dosyasını bul
        $apkPath = $projectDir . '/app/build/outputs/apk/release/app-release-unsigned.apk';
        if (!file_exists($apkPath)) {
            $apkFiles = glob($projectDir . '/app/build/outputs/apk/**/*.apk');
            if (!empty($apkFiles)) {
                $apkPath = end($apkFiles);
            }
        }
        
        if (file_exists($apkPath)) {
            $finalPath = $buildDir . '/apk/build_' . $buildId . '.apk';
            copy($apkPath, $finalPath);
            log_msg("APK oluşturuldu: {$finalPath}");
            
            // DB güncelle
            $stmt = $db->prepare("UPDATE module_mobilebuilder_builds SET status = 'completed', download_path = ?, file_size = ? WHERE id = ?");
            $stmt->execute([$finalPath, filesize($finalPath), $buildId]);
            log_msg("Veritabanı güncellendi");
        } else {
            throw new Exception("APK dosyası bulunamadı!");
        }
    }
    
    log_msg("[6/6] Build tamamlandı!");
    update_status('completed');
    log_msg("=== Build Runner Finished Successfully ===");
    
} catch (Exception $e) {
    log_msg("ERROR: " . $e->getMessage());
    log_msg("=== Build Runner Failed ===");
    
    // Hata logunu kaydet
    $stmt = $db->prepare("UPDATE module_mobilebuilder_builds SET status = 'failed', build_log = ? WHERE id = ?");
    $stmt->execute([json_encode(['error' => $e->getMessage()]), $buildId]);
    
    update_status('failed');
}

// Geçici dosyaları temizle (isteğe bağlı)
// exec("rm -rf {$projectDir}");

/**
 * Minimal Android proje oluştur
 */
function create_minimal_android_project($dir, $projectData) {
    $packageName = $projectData['package_name'] ?? 'com.example.app';
    $appName = $projectData['name'] ?? 'MobilApp';
    
    // Dizin yapısı
    $dirs = [
        $dir . '/app/src/main/java/' . str_replace('.', '/', $packageName),
        $dir . '/app/src/main/res/layout',
        $dir . '/app/src/main/res/values',
        $dir . '/app/src/main/res/drawable',
        $dir . '/app/src/main/res/mipmap-hdpi',
        $dir . '/app/src/main/res/mipmap-mdpi',
        $dir . '/app/src/main/res/mipmap-xhdpi',
        $dir . '/app/src/main/res/mipmap-xxhdpi',
        $dir . '/app/src/main/res/mipmap-xxxhdpi',
    ];
    
    foreach ($dirs as $d) {
        @mkdir($d, 0755, true);
    }
    
    // settings.gradle
    file_put_contents($dir . '/settings.gradle', "
pluginManagement {
    repositories {
        google()
        mavenCentral()
        gradlePluginPortal()
    }
}
dependencyResolutionManagement {
    repositoriesMode.set(RepositoriesMode.FAIL_ON_PROJECT_REPOS)
    repositories {
        google()
        mavenCentral()
    }
}
rootProject.name = \"{$appName}\"
include(\":app\")
");
    
    // build.gradle (root)
    file_put_contents($dir . '/build.gradle', "
plugins {
    id 'com.android.application' version '8.1.0' apply false
    id 'org.jetbrains.kotlin.android' version '1.9.0' apply false
}
");
    
    // gradle.properties
    file_put_contents($dir . '/gradle.properties', "
org.gradle.jvmargs=-Xmx2048m -Dfile.encoding=UTF-8
android.useAndroidX=true
android.enableJetifier=true
android.nonTransitiveRClass=true
");
    
    // gradle-wrapper.properties
    @mkdir($dir . '/gradle/wrapper', 0755, true);
    file_put_contents($dir . '/gradle/wrapper/gradle-wrapper.properties', "
distributionBase=GRADLE_USER_HOME
distributionPath=wrapper/dists
distributionUrl=https\://services.gradle.org/distributions/gradle-8.0-bin.zip
zipStoreBase=GRADLE_USER_HOME
zipStorePath=wrapper/dists
");
    
    // gradlew
    file_put_contents($dir . '/gradlew', "#!/usr/bin/env sh
cd \"$(dirname \"$0\")\" || exit 1
./gradlew \"\$@\"
");
    chmod($dir . '/gradlew', 0755);
    
    // app/build.gradle
    $appBuildGradle = "
plugins {
    id 'com.android.application'
    id 'org.jetbrains.kotlin.android'
}

android {
    namespace '{$packageName}'
    compileSdk 34

    defaultConfig {
        applicationId \"{$packageName}\"
        minSdk 21
        targetSdk 34
        versionCode 1
        versionName \"1.0.0\"

        testInstrumentationRunner \"androidx.test.runner.AndroidJUnitRunner\"
    }

    buildTypes {
        release {
            minifyEnabled false
            proguardFiles getDefaultProguardFile('proguard-android-optimize.txt'), 'proguard-rules.pro'
        }
    }
    
    compileOptions {
        sourceCompatibility JavaVersion.VERSION_17
        targetCompatibility JavaVersion.VERSION_17
    }
    
    kotlinOptions {
        jvmTarget = '17'
    }
}

dependencies {
    implementation 'androidx.core:core-ktx:1.12.0'
    implementation 'androidx.appcompat:appcompat:1.6.1'
    implementation 'com.google.android.material:material:1.11.0'
    implementation 'androidx.constraintlayout:constraintlayout:2.1.4'
    testImplementation 'junit:junit:4.13.2'
    androidTestImplementation 'androidx.test.ext:junit:1.1.5'
    androidTestImplementation 'androidx.test.espresso:espresso-core:3.5.1'
}
";
    file_put_contents($dir . '/app/build.gradle', $appBuildGradle);
    
    // proguard-rules.pro
    file_put_contents($dir . '/app/proguard-rules.pro', "# Add project specific ProGuard rules here.");
    
    // Main Activity
    $activityPath = $dir . '/app/src/main/java/' . str_replace('.', '/', $packageName) . '/MainActivity.kt';
    $mainActivity = "
package {$packageName}

import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity

class MainActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)
    }
}
";
    file_put_contents($activityPath, $mainActivity);
    
    // activity_main.xml
    file_put_contents($dir . '/app/src/main/res/layout/activity_main.xml', "
<?xml version=\"1.0\" encoding=\"utf-8\"?>
<LinearLayout xmlns:android=\"http://schemas.android.com/apk/res/android\"
    android:layout_width=\"match_parent\"
    android:layout_height=\"match_parent\"
    android:orientation=\"vertical\"
    android:gravity=\"center\">

    <TextView
        android:layout_width=\"wrap_content\"
        android:layout_height=\"wrap_content\"
        android:text=\"{$appName}\"
        android:textSize=\"24sp\"
        android:textStyle=\"bold\" />

    <TextView
        android:layout_width=\"wrap_content\"
        android:layout_height=\"wrap_content\"
        android:layout_marginTop=\"16dp\"
        android:text=\"Ahost One ile oluşturuldu\"
        android:textSize=\"14sp\" />

</LinearLayout>
");
    
    // AndroidManifest.xml
    file_put_contents($dir . '/app/src/main/AndroidManifest.xml', "
<?xml version=\"1.0\" encoding=\"utf-8\"?>
<manifest xmlns:android=\"http://schemas.android.com/apk/res/android\"
    xmlns:tools=\"http://schemas.android.com/tools\">

    <uses-permission android:name=\"android.permission.INTERNET\" />
    <uses-permission android:name=\"android.permission.ACCESS_NETWORK_STATE\" />

    <application
        android:allowBackup=\"true\"
        android:dataExtractionRules=\"@xml/data_extraction_rules\"
        android:fullBackupContent=\"@xml/backup_rules\"
        android:icon=\"@mipmap/ic_launcher\"
        android:label=\"{$appName}\"
        android:roundIcon=\"@mipmap/ic_launcher_round\"
        android:supportsRtl=\"true\"
        android:theme=\"@style/Theme.AppCompat.Light.DarkActionBar\"
        tools:targetApi=\"31\">
        
        <activity
            android:name=\".MainActivity\"
            android:exported=\"true\">
            <intent-filter>
                <action android:name=\"android.intent.action.MAIN\" />
                <category android:name=\"android.intent.category.LAUNCHER\" />
            </intent-filter>
        </activity>
    </application>

</manifest>
");
    
    // colors.xml
    file_put_contents($dir . '/app/src/main/res/values/colors.xml', "
<?xml version=\"1.0\" encoding=\"utf-8\"?>
<resources>
    <color name=\"purple_200\">#FFBB86FC</color>
    <color name=\"purple_500\">#FF6200EE</color>
    <color name=\"purple_700\">#FF3700B3</color>
    <color name=\"teal_200\">#FF03DAC5</color>
    <color name=\"teal_700\">#FF018786</color>
    <color name=\"black\">#FF000000</color>
    <color name=\"white\">#FFFFFFFF</color>
</resources>
");
    
    // themes.xml
    file_put_contents($dir . '/app/src/main/res/values/themes.xml', "
<?xml version=\"1.0\" encoding=\"utf-8\"?>
<resources xmlns:tools=\"http://schemas.android.com/tools\">
    <style name=\"Theme.AppCompat.Light.DarkActionBar\" parent=\"Theme.MaterialComponents.DayNight.DarkActionBar\">
        <item name=\"colorPrimary\">@color/purple_500</item>
        <item name=\"colorPrimaryVariant\">@color/purple_700</item>
        <item name=\"colorOnPrimary\">@color/white</item>
        <item name=\"colorSecondary\">@color/teal_200</item>
        <item name=\"colorSecondaryVariant\">@color/teal_700</item>
        <item name=\"colorOnSecondary\">@color/black</item>
    </style>
</resources>
");
    
    // strings.xml
    file_put_contents($dir . '/app/src/main/res/values/strings.xml', "
<?xml version=\"1.0\" encoding=\"utf-8\"?>
<resources>
    <string name=\"app_name\">{$appName}</string>
</resources>
");
    
    // XML dosyaları
    @mkdir($dir . '/app/src/main/res/xml', 0755, true);
    file_put_contents($dir . '/app/src/main/res/xml/data_extraction_rules.xml', "
<?xml version=\"1.0\" encoding=\"utf-8\"?>
<data-extraction-rules>
    <cloud-backup>
        <include domain=\"sharedpref\" path=\".\"/>
        <exclude domain=\"sharedpref\" path=\"device.xml\"/>
    </cloud-backup>
</data-extraction-rules>
");
    
    file_put_contents($dir . '/app/src/main/res/xml/backup_rules.xml', "
<?xml version=\"1.0\" encoding=\"utf-8\"?>
<full-backup-content>
    <include domain=\"sharedpref\" path=\".\"/>
    <exclude domain=\"sharedpref\" path=\"device.xml\"/>
</full-backup-content>
");
    
    // Gradle wrapper JAR indir (ilk çalıştırmada)
    log_msg("Gradle wrapper hazırlanıyor...");
    exec("cd {$dir} && if [ ! -f gradle/wrapper/gradle-wrapper.jar ]; then mkdir -p gradle/wrapper && curl -sL -o gradle/wrapper/gradle-wrapper.jar https://raw.githubusercontent.com/gradle/gradle/v8.0.0/gradle/wrapper/gradle-wrapper.jar; fi");
}
