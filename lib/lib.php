 <?php
  function placeFile(){
        try {

            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (
                !isset($_FILES['upfile']['error']) ||
                is_array($_FILES['upfile']['error'])
            ) {
                echo $_FILES['upfile']['error'];
                throw new RuntimeException('Invalid parameters.');
            }

            // Check $_FILES['upfile']['error'] value.
            switch ($_FILES['upfile']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException('No file sent.');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('Exceeded filesize limit. '.$_FILES['upfile']['size']);
                default:
                    throw new RuntimeException('Unknown errors.');
            }

            // You should also check filesize here.
            if ($_FILES['upfile']['size'] > (100*1024*1024)) {

                throw new RuntimeException('Exceeded filesize limit. '.$_FILES['upfile']['size']);
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $allowed_file_types = array(
                    'war' => 'application/zip',
                    'zip' => 'application/zip',
                    'rar' => 'application/x-rar'
                );

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search(
                $mime_type = $finfo->file($_FILES['upfile']['tmp_name']),
                $allowed_file_types,
                true
            )) {
                throw new RuntimeException('Invalid file format.');
            } else if($ext != $uploaded_ext = pathinfo($_FILES['upfile']['name'], PATHINFO_EXTENSION)){
                if(array_key_exists(
                    $uploaded_ext,
                    $allowed_file_types) && $allowed_file_types[$uploaded_ext] == $mime_type
                ){
                    $ext = $uploaded_ext;
                }
            }

            // You should name it uniquely.
            // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.

            $deploy_date = new DateTime($_POST['action_date'], new DateTimeZone('Asia/Ulaanbaatar'));
            if(!file_exists('../../backups')) {
                mkdir('../../backups/'.$deploy_date->format('Y-m-d').'/'.$deploy_date->format('H-i-s'), 0777, true);
            } else if(!file_exists('../../backups/'.$deploy_date->format('Y-m-d'))) {
                mkdir('../../backups/'.$deploy_date->format('Y-m-d').'/'.$deploy_date->format('H-i-s'), 0777, true);
            } else if(!file_exists('../../backups/'.$deploy_date->format('Y-m-d').'/'.$deploy_date->format('H-i-s'))) {
                mkdir('../../backups/'.$deploy_date->format('Y-m-d').'/'.$deploy_date->format('H-i-s'));
            }


            if (!move_uploaded_file(
                $_FILES['upfile']['tmp_name'],
                sprintf('../../backups/%s/%s/%s',
                    $deploy_date->format('Y-m-d'),
                    $deploy_date->format('H-i-s'),
                    $_FILES['upfile']['name']
                )
            )) {
                throw new RuntimeException('Failed to move uploaded file.');
            }

            $_POST['ext'] = $ext;

            return 0;

        } catch (RuntimeException $e) {

            return $e->getMessage();

        }
    }

    function deleteBackup($d){
        $deploy_date = new DateTime($d['action_date'], new DateTimeZone('Asia/Ulaanbaatar'));
        if(file_exists('../../backups/'.$deploy_date->format('Y-m-d').'/'.$deploy_date->format('H-i-s').'/'.$d['filename'])) {
            if(@unlink('../../backups/'.$deploy_date->format('Y-m-d').'/'.$deploy_date->format('H-i-s').'/'.$d['filename'])) {
                if(is_dir_empty('../../backups/'.$deploy_date->format('Y-m-d').'/'.$deploy_date->format('H-i-s'))) {
                    if(@rmdir('../../backups/'.$deploy_date->format('Y-m-d').'/'.$deploy_date->format('H-i-s'))) {
                        if(is_dir_empty('../../backups/'.$deploy_date->format('Y-m-d'))) {
                            @rmdir('../../backups/'.$deploy_date->format('Y-m-d'));
                        }
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }

    }

    function is_dir_empty($dir) {
      if (!is_readable($dir)) return NULL;
      return (count(scandir($dir)) == 2);
    }
