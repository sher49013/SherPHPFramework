<?php
/**
 * Filesystem operations.
 *
 *
 * @author HAMAD ALI (ali sher)
 */


/**
 * Opens a file and returns its content.
 * Read with simple file methods, "file_get_contents" is available from 4.3.0.
 *
 * @param	string	$filename
 * @return	string
 */
function io_file_get_contents($filename) {

	if(function_exists('file_get_contents')) {
		$content = file_get_contents($filename);
	} else {
		$content = '';

		$fp = fopen($filename, 'r');
		while (!feof($fp)) {
			$content .= fgets($fp, 8192);
		}
		fclose($fp);
	}
	
	return $content;

}


/**
 * Write file.
 *
 * @param	string	$filename
 * @param	string	$content
 * @param	int	$mode
 * @return	bool
 */
function io_file_put_contents($filename, $content, $mode=0) {
	if(function_exists('file_put_contents')) {
		$result = @file_put_contents($filename, $content);
		
		if(isset($GLOBALS['log']) && is_object($GLOBALS['log'])) {
			if($result == true) {
				$GLOBALS['log']->debug('Wrote file "'.$filename.'".');
			} else {
				$GLOBALS['log']->debug('Could not write file "'.$filename.'".');
			}
		}
		
		return $result;
		
	} else {
		if(($fp = @fopen($filename, 'w')) === false) {
			if(isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->debug('Could not write file "'.$filename.'".');
			
			return false;
		}

		if(($bytes = @fwrite($fp, $content)) === false) {
			if(isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->debug('Could not write file "'.$filename.'".');
			
			return false;
		}

		fclose($fp);
		
		if($mode != 0) io_chmod($filename, $mode);
		
		if(isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->debug('Wrote file "'.$filename.'".');
		
		return true;
	}
}


/**
 * Is file or directory in another directory ?
 *
 * @param	string
 * @param	string
 * @return	bool
 */
function io_is_in_directory($find, $root_dir) {

	// File or directory ?
	if(!io_is_dir($find)) $find = dirname($find);

	// Prepare directories
	$find = @realpath($find);
	$root_dir = @realpath($root_dir);
        
	// Cannot be..
	if(strlen($find) < strlen($root_dir)) return false;
	if($find == '' || $root_dir == '') return false;

	// Compare
	if(substr($find, 0, strlen($root_dir)) == $root_dir) {
		return true;
	} else {
		return false;
	}

}


/**
 * Returns a list of files matching a pattern string in a directory and its subdirectories.
 *
 * @link	http://de2.php.net/manual/en/reserved.constants.php
 *
 * @param	string
 * @param	string
 * @param	int	Maximum number of recursion levels, -1 for infinite
 * @param	int	Recursion level
 * @param	bool	Return folders also ?
 * @param	mixed	List of files or directories that will be ignored
 * @return	mixed
 */
function io_search_directory($pattern, $dir, $maxlevel=0, $level=0, $return_directories=false, $ignore_list=0) {
	$result = array();

	if($level > $maxlevel && $maxlevel != -1) return;
	if(substr($dir, -1) == DIRECTORY_SEPARATOR || substr($dir, -1) == '/') {
		$dir = substr($dir, 0, -1);
	}

	if(io_is_dir($dir)) {
		if($dh = opendir($dir)) {
			while(($file = readdir($dh)) !== false) {
				if(is_array($ignore_list)) {
					if(in_array($file, $ignore_list)) $file = '.'; // Mark to be ignored
				}
				
				if($file != '.' && $file != '..') {
					if(io_is_dir($dir.DIRECTORY_SEPARATOR.$file)) {
						$test_return = io_search_directory($pattern, $dir.DIRECTORY_SEPARATOR.$file, $maxlevel, $level + 1, $return_directories);

						if(is_array($test_return)) {
							$temp = array_merge($test_return, $result);
							$result = $temp;
						}

						if(is_string($test_return)) {
							array_push($result, $test_return);
						}
						
						if($return_directories == true) {
							$add_it = false;
							
							if($pattern == '/.*/' || $pattern == '') {
								$add_it = true;	
							} elseif(preg_match($pattern, $file)) {
								$add_it = true;
							}
							
							if($add_it) array_push($result, $dir.DIRECTORY_SEPARATOR.$file);
						}

					} else {
						$add_it = false;
						
						if($pattern == '/.*/' || $pattern == '') {
							$add_it = true;	
						} elseif(preg_match($pattern, $file)) {
							$add_it = true;
						}

						if($add_it) array_push($result, $dir.DIRECTORY_SEPARATOR.$file);
					}
				}
			}

			closedir($dh);
		}
	}

	return $result;
}


/**
 * Returns real absolute path of a given path name.
 *
 * @param	string	$path
 * @return	string
 */
function io_real_path($path) {
	if($path == '') return false;
	
	$path = trim(preg_replace('/\\\\/', '/', (string)$path));

	if(!preg_match('/(\.\w{1,4})$/', $path)  && !preg_match('/\?[^\\/]+$/', $path)  &&  !preg_match('/\\/$/', $path)) {
		$path .= '/';
	}

	$pattern = '/^(\\/|\w:\\/|https?:\\/\\/[^\\/]+\\/)?(.*)$/i';

	preg_match_all($pattern, $path, $matches, PREG_SET_ORDER);

	$path_tok_1 = $matches[0][1];
	$path_tok_2 = $matches[0][2];

	$path_tok_2 = preg_replace(
			array('/^\\/+/', '/\\/+/'),
			array('', '/'),
			$path_tok_2);

	$path_parts = explode('/', $path_tok_2);
	$real_path_parts = array();

	for ($i = 0, $real_path_parts = array(); $i < count($path_parts); $i++) {
		if($path_parts[$i] == '.') {
			continue;
		} elseif($path_parts[$i] == '..') {
			if((isset($real_path_parts[0])  &&  $real_path_parts[0] != '..') || ($path_tok_1 != '')) {
				array_pop($real_path_parts);
				continue;
			}
		}
		
		array_push($real_path_parts, $path_parts[$i]);
	}

	return $path_tok_1.implode('/', $real_path_parts);
}


/**
 * Is directory.
 *
 * @param	string	$dir
 * @param	bool	$case_sensitive
 * @return	bool
 */
function io_is_dir($dir, $case_sensitive=false) {
	if($case_sensitive == false) {
		return is_dir($dir) ? true : false;
	} else {
		$basename = strtolower(basename($dir));
		$parent = dirname($dir);
		$list = scandir($parent);

		foreach($list as $file) {
			if(strtolower($file) == $basename) {
				if(@is_dir($parent.DIRECTORY_SEPARATOR.$file)) {
					return true;
				}
			}
			
		}

		return false;
	}
}


/**
 * File exists.
 *
 * @param	string	$file
 * @param	bool	$case_sensitive
 * @return	bool
 */
function io_file_exists($file, $case_sensitive=false) {
	if($GLOBALS['env']['is_windows']) $file = str_replace('/', "\\", $file);

	if($case_sensitive == false) {
		return @file_exists($file) ? true : false;
	} else {
		$basename = strtolower(basename($file));
		$parent = dirname($file);
		$list = scandir($parent);

		foreach($list as $item) {
			if(strtolower($item) == $basename) {
				return true;
			}
		}

		return false;
	}
}


/**
 * Returns if a file is writable.
 *
 * @param	string	$file
 * @return	bool
 */
function io_is_writable($file) {
	if(!io_file_exists($file)) {
		$fp = @fopen($file, 'w');
		if(!$fp) {
			return false;
		} else {
			fclose($fp);
			@unlink($file);
			clearstatcache();

			return true;
		}
	} else {
		return is_writable($file);	// Known problem: is_writeable sometimes returns bad values on Windows
	}	
}


/**
 * Returns file size.
 *
 * @param	string	$filename
 * @return	int
 */
function io_get_file_size($filename) {
	if(io_file_exists($filename)) {
		return filesize($filename);
	} else {
		return 0;
	}
}


/**
 * Returns file modification time.
 *
 * @param	string	$filename
 * @return	int
 */
function io_get_file_modified($filename) {
	if(io_file_exists($filename)) {
		return filemtime($filename);
	} else {
		return 0;
	}
}


/**
 * Returns file creation time.
 *
 * @param	string	$filename
 * @return	int
 */
function io_get_file_created($filename) {
	if(io_file_exists($filename)) {
		return filectime($filename);
	} else {
		return 0;
	}
}


/**
 * Creates a directory.
 *
 * @param	string	$path
 * @param	bool	$recursive
 * @param	int	$mode
 * @return	int
 */
function io_mkdir($path, $recursive=false, $mode=0) {
	$result = 0;
	
	if($recursive == false) {
		$result = @mkdir($path);
		if(!$result) {
			$GLOBALS['log']->error('Could not create directory "'.$path.'"');
		} else {
			if($mode != 0) io_chmod($path, $mode);
			
			if(isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->debug('Created directory "'.$path.'"');
		}
	} else {
		$folder_path = array($path);

		while(!@is_dir(dirname(end($folder_path))) && dirname(end($folder_path)) != '/' && dirname(end($folder_path)) != '.' && dirname(end($folder_path)) != '') {
			array_push($folder_path, dirname(end($folder_path)));
		}

		while($parent_folder_path = array_pop($folder_path)) {
			$result = @mkdir($parent_folder_path);
			if(!$result) {
				$GLOBALS['log']->error('Could not create directory "'.$parent_folder_path.'"');
				break;
			} else {
				if($mode != 0) io_chmod($parent_folder_path, $mode);
				
				if(isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->debug('Created directory "'.$parent_folder_path.'"');
			}
		}
	}
	
	return $result;
}


/**
 * Removes files and directories recursively.
 * 
 * @param	string	$path
 * @return	void
 */
function io_delete_files($path) {
	if(io_is_dir($path)) {
		if(substr($path, -1) == DIRECTORY_SEPARATOR) $path = substr($path, 0, strlen($path) - 1);
		
		$dir_contents = scandir($path);
		foreach($dir_contents as $item) {
			if($item != '.' && $item != '..') {
				if(io_is_dir($path.DIRECTORY_SEPARATOR.$item)) {
					io_delete_files($path.DIRECTORY_SEPARATOR.$item);
		   		} else {
		       			io_delete_file($path.DIRECTORY_SEPARATOR.$item);
		   		}
		   	}
		}
		
		io_delete_directory($path, false);
	} else {
		io_delete_file($path);
	}
}



/**
 * Remove a single directory.
 * 
 * @param	string	$directory
 * @param	bool	$recursive
 * @return	bool
 */
function io_delete_directory($directory, $recursive=false) {
	if(isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->debug('Removing directory '.$directory);

	if($recursive == true) {
		io_delete_files($directory);
	}
	
	@rmdir($directory);
	clearstatcache();
	
	return io_is_dir($directory) ? false : true;
}


/**
 * Remove a single file.
 * 
 * @param	string	$file
 * @return	bool
 */
function io_delete_file($file) {
	if(isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->debug('Removing file '.$file);
	
	@unlink($file);
	clearstatcache();
	
	return !io_file_exists($file);
}


/**
 * Copy a file.
 * 
 * @param	string	$file1
 * @param	string	$file2
 * @return	bool
 */
function io_file_copy($file1, $file2) {
	if(isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->debug('Copying file "'.$file1.'" to "'.$file2.'"');
	
	if(io_file_exists($file1) == false) {
		$GLOBALS['log']->error('Copy failed. Source file "'.$file1.'" does not exist.');
		return false;
	}
	
	if(realpath($file1) == realpath($file2)) {
		$GLOBALS['log']->error('Copy failed. Source and target are identical: "'.$file1.'" / "'.$file2.'"');
		return false;
	}
	
	$result = @copy($file1, $file2);
	if(isset($GLOBALS['log']) && $result == false) $GLOBALS['log']->debug('Copying file "'.$file1.'" failed.');
	return $result;
}


/**
 * Move a file.
 * 
 * @param	string	$file1
 * @param	string	$file2
 * @return	bool
 */
function io_file_move($file1, $file2) {
	if(isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->debug('Moving file "'.$file1.'" to "'.$file2.'"');
	
	if(io_file_exists($file2) && $GLOBALS['env']['is_windows']) io_delete_file($file2); // On Unix/Linux "rename" overwrites existing files, on Windows not
	
	$result = @rename($file1, $file2);
	if(isset($GLOBALS['log']) && $result == false) $GLOBALS['log']->debug('Moving file "'.$file1.'" failed.');
	return $result;
}


/**
 * Copy a directory with all its files and subdirectories.
 *
 * @param	string	$srcdir
 * @param	string	$dstdir
 * @param	string	$ignore_pattern
 * @return	int
 */
function io_dir_copy($srcdir, $dstdir, $ignore_pattern='') {
	$num = 0;
	if(@is_dir($dstdir) === false) io_mkdir($dstdir);
	
	if($curdir = opendir($srcdir)) {
		while($file = readdir($curdir)) {
			if($file != '.' && $file != '..') {
				if($ignore_pattern == '' || preg_match($ignore_pattern, $file) == false) {
					$srcfile = $srcdir.DIRECTORY_SEPARATOR.$file;
					$dstfile = $dstdir.DIRECTORY_SEPARATOR.$file;
					if(is_file($srcfile)) {
						if(is_file($dstfile)) {
							$ow = filemtime($srcfile) - filemtime($dstfile);
						} else {
							$ow = 1;
						}
						if($ow > 0) {
							if(io_file_copy($srcfile, $dstfile)) {
								@touch($dstfile, filemtime($srcfile)); $num++;
							}
						}                 
					} elseif(@is_dir($srcfile)) {
						$num += io_dir_copy($srcfile, $dstfile, $ignore_pattern);
					}
				}
			}
		}
		closedir($curdir);
	}
	
	return $num;
}


/** 
 * Returns a file's extension.
 *
 * @param	string	$filename
 * @return	string
 */
function io_get_file_extension($filename) {
	$exploded = explode('.', $filename);
	return strtolower(end($exploded));
}


/**
 * Changes file permissions.
 *
 * @param	string	$filename
 * @param	int $mode
 * @return	bool
 */
function io_chmod($filename, $mode) {
	if(isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->debug('Modified file permissions of "'.$filename.'" to '.$mode);
	return @chmod($filename, $mode);
}


/**
 * Rename a file or directory.
 *
 * @param	string	$old_file
 * @param	string	$new_file
 * @return	void
 */
function io_rename_file($old_file, $new_file) {
	if(isset($GLOBALS['log']) && is_object($GLOBALS['log'])) $GLOBALS['log']->debug('Renamed file "'.$old_file.'" to '.$new_file);
	@rename($old_file, $new_file);
}


/**
 * Check if file is uploaded file. PHP default function does 
 * not work reliable on Windows systems.
 * 
 * @see		http://de.php.net/is_uploaded_file
 *
 * @param	string	$filename
 * @return	bool
 */
function io_is_uploaded_file($filename) {
	if(is_uploaded_file($filename)) {
		return true;
	} else {
		if(!$tmp_file = get_cfg_var('upload_tmp_dir')) {
			$tmp_file = dirname(tempnam('', ''));
			if($tmp_file == '.' || $tmp_file == '') {
				$tmp_file = '/tmp';
			}
		}
		$tmp_file .= '/'.basename($filename);

		// Fix for win platform: User might have trailing slash in php.ini
		$filename = str_replace('\\', '/', $filename);
		$tmp_file = str_replace('\\', '/', $tmp_file);

		return (preg_replace('/+', '/', $tmp_file) == $filename);
	}
}


/**
 * Returns the file type.
 *
 * Possible values are:
 * - folder
 * - image
 * - sound
 * - video
 * - ms_word
 * - ms_excel
 * - ms_powerpoint
 * - pdf
 * - quicktime
 * - realplayer
 * - text
 * - flash
 *
 * Default return value is "document"
 *
 * @param	string	$filename
 * @param	bool	$exists_check
 * @return	string
 */
function io_get_file_type($filename, $exists_check=true) {

	if(io_file_exists($filename) || $exists_check == false) {

		if(io_is_dir($filename)) {
			return 'folder';
		} else {
			switch(io_get_file_extension($filename)) {
				case 'jpg':
					return 'image';
					break;
				case 'jpeg':
					return 'image';
					break;
				case 'gif':
					return 'image';
					break;
				case 'png':
					return 'image';
					break;
				case 'txt':
					return 'text';
					break;
				case 'htm':
					return 'html';
					break;
				case 'html':
					return 'html';
					break;
				case 'ppt':
					return 'ms_powerpoint';
					break;
				case 'xls':
					return 'ms_excel';
					break;
				case 'doc':
					return 'ms_word';
					break;
				case 'rm':
					return 'realaudio';
					break;
				case 'ra':
					return 'realaudio';
					break;
				case 'ram':
					return 'realaudio';
					break;
				case 'pdf':
					return 'pdf';
					break;
				case 'mov':
					return 'quicktime';
					break;
				case 'qt':
					return 'quicktime';
					break;
				case 'swf':
					return 'flash';
					break;
				case 'avi':
					return 'video';
					break;
				case 'mpeg':
					return 'video';
					break;
				case 'mpg':
					return 'video';
					break;
				case 'wmv':
					return 'video';
					break;
				case 'mp3':
					return 'sound';
					break;
				case 'wav':
					return 'sound';
					break;
				case 'mid':
					return 'sound';
					break;
				case 'exe':
					return 'application';
					break;
				case 'xml':
					return 'xml';
					break;
				case 'zip':
					return 'archive';
					break;
				case 'tar':
					return 'archive';
					break;
				case 'tgz':
					return 'archive';
					break;
				case 'gz':
					return 'archive';
					break;
				default:
					return 'document';
			}
		}
		
	} else {
		return '';
	}
	
}

if(!function_exists('scandir')) {
	/**
	 * Scandir for PHP4.
	 */
	function scandir($dir, $sortorder=0) {
		if(@is_dir($dir)) {
			$files = array();
			$dirlist = opendir($dir);
			while( ($file = readdir($dirlist)) !== false) {
				$files[] = $file;
			}
			($sortorder == 0) ? sort_asort($files) : sort_rsort($files); // arsort was replaced with rsort
			return $files;
		} else {
			return array();
		}
	}
}


if(!function_exists('mime_content_type')) {

	/**
	 * Get mime type for a file.
	 *
	 * @param	string	$filename
	 * @return	string
	 */
 
	function mime_content_type($filename) {
		$mime_types = array (
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'eml' => 'message/rfc822',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);

		$ext = io_get_file_extension($filename);

		if(isset($mime_types[$ext])) {
			return $mime_types[$ext];
		} elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		} else {
			return 'application/octet-stream';
		}
	}
}


/**
 * Extracts a zip file to a directory.
 *
 * @param	string	$archive_file
 * @param	string	$target_dir
 * @return	bool
 */
function zip_extract_file($archive_file, $target_dir) {
	if(class_exists('ZipArchive')) {
		$zip = new ZipArchive();
		$result = $zip->open($archive_file);
		if($result == true) {
			$zip->extractTo($target_dir);
			$zip->close();
			unset($zip);
			return true;
		} else {
			$GLOBALS['log']->error('Could not open zip file "'.$archive_file.'" (ZipArchive): '.$result);
			return false;
		}
	} else {
		$archive = new PclZip($archive_file);

		if($archive->extract($target_dir, PCLZIP_OPT_ADD_TEMP_FILE_ON) == 0) {
			$GLOBALS['log']->error('Zip file extraction failed: '.$archive->errorInfo(true));
			return false;
		} else {
			return true;
		}
	}
}

?>