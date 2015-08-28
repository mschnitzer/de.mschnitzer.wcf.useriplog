<?php
 namespace wcf\system\event\listener;
 use wcf\system\event\IEventListener;
 use wcf\system\WCF;
 use wcf\util\UserUtil;
 
 /**
  * Collecting the IP address and the user agent from the current user
  *
  * @author      Manuel Schnitzer
  * @copyright   2015-2015 Manuel Schnitzer
  * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
  * @package     com.woltlab.wcf
  * @category    Community Framework
  */
 class UserIPLogFetchUserIPListener implements IEventListener {
	/**
	* @see \wcf\system\event\IEventListener::execute()
	*/
	public function execute($eventObj, $className, $eventName) {
		// ignore guests
		if (!WCF::getUser()->userID) {
			return 0;
		}
		
		// check if there is already an entry in the database
		$sql = "SELECT COUNT(entryID) AS count FROM wcf".WCF_N."_user_iplog
			WHERE
				userID = ? AND
				ipAddress = ? AND
				userAgent = ?;";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			WCF::getUser()->userID,
			UserUtil::getIpAddress(),
			UserUtil::getUserAgent()
		));
		
		$row = $statement->fetchArray();
		if (!$row['count']) {
			// no entry was found => insert a new one
			$sql = "INSERT INTO wcf".WCF_N."_user_iplog
				 (userID, ipAddress, userAgent, timestamp)
				VALUES
				 (?, ?, ?, ?);";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				WCF::getUser()->userID,
				UserUtil::getIpAddress(),
				UserUtil::getUserAgent(),
				TIME_NOW
			));
		}
		
		return 1;
	}
 }