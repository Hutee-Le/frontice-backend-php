<?php

namespace App\Services;

use DateTime;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Storage;
use Illuminate\Support\Facades\Log;

class FirebaseService extends Service
{
    protected $storage;
    // public function __construct()
    // {
    //     $firebase = (new Factory())
    //         ->withServiceAccount(config('firebase.credentials.file'))
    //         ->withDefaultStorageBucket(config('firebase.storage.bucket'));
    //     $this->storage = $firebase->createStorage();;
    // }
    public function __construct()
    {
        $credentials = config('firebase.credentials');

        // Kiểm tra và xử lý lỗi nếu credentials không hợp lệ
        if (empty($credentials) || !is_array($credentials)) {
            throw new \Exception('Firebase credentials are not set properly.');
        }

        // Kết nối với Firebase Storage
        $firebase = (new Factory())
            ->withServiceAccount($credentials)
            ->withDefaultStorageBucket(config('firebase.storage.bucket'));

        $this->storage = $firebase->createStorage();
    }


    /**
     * Upload a file to Firebase Storage
     *
     * @param $file
     * @param string $path
     * @return string 
     */
    public function uploadFile($file, string $path, $file_name = null): string
    {
        $fileName = $file_name ?? time() . $file->getClientOriginalName();
        $filePath = (str_ends_with($path, "/") ? $path : $path . '/') . $fileName;
        $bucket = $this->storage->getBucket();
        $bucket->upload(fopen($file->getRealPath(), 'r'), ['name' => $filePath]);
        return "{$filePath}";
    }


    public function sign($filePath, \DateTime $expiration = new DateTime('+1 day'))
    {
        if ($filePath) {
            // Get the bucket
            $bucket = $this->storage->getBucket();

            // Create the object for the specified file
            $object = $bucket->object($filePath);
            // Generate the signed URL
            $signedUrl = $object->signedUrl($expiration);

            return $signedUrl;
        } else {
            return null;
        }
    }
    public function filesExists(array $filePaths)
    {
        foreach ($filePaths as $filePath) {
            if ($filePath !== null && $filePath !== "") {
                if (!$this->storage->getBucket()->object($filePath)->exists()) {
                    return [
                        'results' => false,
                        'filePath' => $filePath
                    ];
                }
            }
        }
        return ['results' => true];
    }
    public function renameFile($oldFilePath, $newFilePath)
    {
        // Lấy bucket
        $bucket = $this->storage->getBucket();

        // Tạo đối tượng cho file cũ
        $oldObject = $bucket->object($oldFilePath);

        // Kiểm tra xem file cũ có tồn tại không
        if ($oldObject->exists()) {
            // Lấy phần mở rộng của file cũ
            $fileInfo = pathinfo($oldFilePath);
            $fileExtension = isset($fileInfo['extension']) ? $fileInfo['extension'] : '';

            // Kiểm tra xem newFilePath có phần mở rộng không
            $newFileInfo = pathinfo($newFilePath);
            if (empty($newFileInfo['extension'])) {
                // Nếu không có, thêm phần mở rộng từ file cũ
                $newFilePath .= ($fileExtension ? '.' . $fileExtension : '');
            }

            // Tạo đối tượng mới cho file đích
            $newObject = $bucket->object($newFilePath);

            // Sao chép nội dung của file cũ sang file mới
            $newObject->copy($oldObject);

            // Xóa file cũ
            $oldObject->delete();

            return true; // Đổi tên và chuyển thành công
        } else {
            // File cũ không tồn tại
            throw new \Exception("File cũ không tồn tại.");
        }
    }





    public function delete($filePath = null): void
    {
        try {
            if ($filePath != null) {
                $bucket = $this->storage->getBucket();
                $object = $bucket->object($filePath);
                $object->delete();
            }
        } catch (\Google\Cloud\Core\Exception\NotFoundException $e) {
            Log::error("Error deleting file: " . $e->getMessage());  // Ghi lỗi vào log
        } catch (\Exception $e) {
            Log::error("General error: " . $e->getMessage());  // Ghi lỗi khác vào log
        }
    }
    public function deleteFolder($folderPath): void
    {
        $bucket = $this->storage->getBucket();

        // Liệt kê và xóa tất cả các file trong folder
        foreach ($bucket->objects(['prefix' => $folderPath]) as $object) {
            $object->delete();
        }
    }
}
