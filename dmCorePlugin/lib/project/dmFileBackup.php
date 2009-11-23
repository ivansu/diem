<?php

class dmFileBackup extends dmConfigurable
{
  protected
  $dispatcher,
  $filesystem;
  
  public function __construct(sfEventDispatcher $dispatcher, dmFilesystem $filesystem, array $options = array())
  {
    $this->dispatcher = $dispatcher;
    $this->filesystem = $filesystem;
    
    $this->initialize($options);
  }
  
  public function initialize(array $options = array())
  {
    $this->configure($options);
    
    $this->checkDirIsWritable();
  }
  
  public function getDefaultOptions()
  {
    return array(
      'dir' => 'data/dm/backup/filesystem'
    );
  }
  
  public function getDir()
  {
    return dmProject::rootify($this->options['dir']);
  }
  
  public function clear()
  {
    return $this->filesystem->deleteDirContent($this->getDir());
  }
  
  /*
   * Backup a file
   * return boolean success
   */
  public function save($file)
  {
    if (!$this->isEnabled())
    {
      return true;
    }
    
    if(!dmProject::isInProject($file))
    {
      $file = dmOs::join(dmProject::getRootDir(), $file);
    }
    
    if (!is_readable($file))
    {
      throw new dmException('Can no read '.$file);
      return false;
    }
    
    $relFile = dmProject::unRootify($file);
    
    $backupPath = dmOs::join($this->getDir(), dirname($relFile));
    
    if(!$this->filesystem->mkdir($backupPath))
    {
      throw new dmException('Can not create backup dir '.$backupPath);
      return false;
    }
    
    $backupFile = dmOs::join($backupPath, basename($relFile).'.'.date('Y-m-d_H-i-s'));
    
    if (!copy($file, $backupFile))
    {
      throw new dmException('Can not copy '.$file.' to '.$backupFile);
      return false;
    }
    
    $this->filesystem->chmod($file, 0777);
    
    return true;
  }
  
  public function getFiles()
  {
    return sfFinder::type('file')->in($this->getDir());
  }
  
  protected function checkDirIsWritable()
  {
    if (!$this->filesystem->mkdir($this->getDir()))
    {
      throw new dmException('dmFileBackup dir is not writable : '.$this->getDir());
    }
  }
  
  
  public function isEnabled()
  {
    return sfConfig::get('dm_backup_enabled');
  }
  
}