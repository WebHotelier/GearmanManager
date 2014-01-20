<?php

/**
 * Implements a job list that protects gearman from executing a job multiple times
 *
 * @author      Luc Oth <luc@everyglobe.com>
 * @package     GearmanManager
 *
 */
class JobFailProtection {

  const MaxTriesPerJobs = 3;
  const CacheForSeconds = 300; // 5 minutes
  const ApcPrefix = 'GearmanManager_';

  /**
   * push a job int the list of executed jobs
   *
   * @param  string $jobId the id of the job
   *
   * @return boolean false if the job and max. number of ties, else true
   */
  public static function pushJob($jobId)
  {
    if (!apc_exists($jobId)) {
      apc_add(self::ApcPrefix.$jobId , -1, self::CacheForSeconds);
    }

    $callCount = apc_inc(self::ApcPrefix.$jobId);

    if ($callCount > JobFailProtection::MaxTriesPerJobs) {
      // notify admins
      mail('geeks@everyglobe.com', 'GearmanManager Reoccuring Job Failure', 'Job with id: '.$jobId.' failed multiple times. Stopped processing.');
      return false;
    }
    return true;
  }

  /**
   * remove a job from the job list as it was successfully processed
   *
   * @param  string $jobId the id of the job
   */
  public static function clearJob($jobId)
  {
    apc_delete(self::ApcPrefix.$jobId);
  }
}
