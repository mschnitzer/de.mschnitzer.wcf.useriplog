<?php
 namespace wcf\system\event\listener;
 use wcf\system\event\IEventListener;
 use wcf\system\WCF;
 use wcf\util\UserUtil;
 use wcf\system\message\quote\MessageQuoteManager;
 use wcf\data\conversation\ConversationAction;

 
 /**
  * Checks if a multi account exists on a new IP
  *
  * @author      Manuel Schnitzer
  * @copyright   2015-2015 Manuel Schnitzer
  * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
  * @package     com.woltlab.wcf
  * @category    Community Framework
  */
 class UserIPLogMultiAccountCheckerListener implements IEventListener {
	/**
	* @see \wcf\system\event\IEventListener::execute()
	*/
	public function execute($eventObj, $className, $eventName) {
		// ignore guests
		if (!WCF::getUser()->userID) {
			return 0;
		}

		// search for accounts with that ip address
		$sql = "SELECT COUNT(userID) AS count FROM wcf".WCF_N."_user_iplog
				WHERE
					userID != ? AND
					ipAddress = ?";

		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			WCF::getUser()->userID,
			UserUtil::getIpAddress(),
		));

		// if there is already an account with that ip address, we'll
		// go ahead and add a new multi account entry
		$row = $statement->fetchArray();
		if ($row['count']) {
			$sql = "SELECT userID, ipAddress FROM wcf".WCF_N."_user_iplog
					WHERE
						userID != ? AND
						ipAddress = ?";

			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				WCF::getUser()->userID,
				UserUtil::getIpAddress(),
			));

			while ($row = $statement->fetchArray()) {
				// check if there is already a multi account entry
				$sql = "SELECT COUNT(entryID) AS count
						FROM wcf".WCF_N."_user_iplog_multiaccounts
						WHERE
							userID = ? AND
							multiaccountID = ? AND
							ipAddress = ?";
				$_statement = WCF::getDB()->prepareStatement($sql);
				$_statement->execute(array(
					WCF::getUser()->userID,
					$row['userID'],
					UserUtil::getIpAddress(),
				));

				$_row = $_statement->fetchArray();
				if (!$_row['count']) {
					// no entry was found so we'll add one
					$sql = "INSERT INTO wcf".WCF_N."_user_iplog_multiaccounts
							 (userID, multiaccountID, ipAddress, timestamp)
							VALUES
							 (?, ?, ?, ?);";
					$_statement = WCF::getDB()->prepareStatement($sql);
					$_statement->execute(array(
						WCF::getUser()->userID,
						$row['userID'],
						UserUtil::getIpAddress(),
						TIME_NOW,
					));

					// send pm
			        $data = array_merge(array(), array(
			            'subject' => 'Test1',
			            'time' => TIME_NOW,
			            'userID' => 0,
			            'username' => 'Guest',
			            'isDraft' => 0,
			            'participantCanInvite' => false
			        ));
			     
			        $conversationData = array(
			            'data' => $data,
			            'attachmentHandler' => null,
			            'messageData' => array(
			                'message' => 'Hallo!!!',
			                'enableBBCodes' => true,
			                'enableHtml' => false,
			                'enableSmilies' => true,
			                'showSignature' => false
			            )
			        );

			        $this->objectAction = new ConversationAction(array(), 'create', $conversationData);
			        $resultValues = $this->objectAction->executeAction();
			     
			        MessageQuoteManager::getInstance()->saved();
				}
			}
		}

		return 1;
	}
 }