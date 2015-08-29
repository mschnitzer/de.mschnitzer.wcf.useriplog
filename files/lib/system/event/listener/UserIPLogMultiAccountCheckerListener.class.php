<?php
 namespace wcf\system\event\listener;
 use wcf\system\event\IEventListener;
 use wcf\system\WCF;
 use wcf\util\UserUtil;
 use wcf\system\message\quote\MessageQuoteManager;
  use wcf\data\conversation\Conversation;
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
 	 * The notification option for groups
 	 */
 	public $iplogReceiveInfoOption = 'mod.iplog.notifications.receiveMultiaccountNotification';

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
			$sql = "SELECT iplog.userID, iplog.ipAddress, users.username
					FROM wcf".WCF_N."_user_iplog AS iplog
					INNER JOIN wcf".WCF_N."_user AS users ON (users.userID = iplog.userID)
					WHERE
						iplog.userID != ? AND
						iplog.ipAddress = ?";

			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				WCF::getUser()->userID,
				UserUtil::getIpAddress(),
			));

			// a list of all multi accounts
			$multiaccounts = array();

			while ($row = $statement->fetchArray()) {
				// check if there is already a multi account entry
				$sql = "SELECT COUNT(entryID) AS count
						FROM wcf".WCF_N."_user_iplog_multiaccounts
						WHERE
							userID = ? AND
							multiaccountID = ?";
				$_statement = WCF::getDB()->prepareStatement($sql);
				$_statement->execute(array(
					WCF::getUser()->userID,
					$row['userID'],
				));

				$_row = $_statement->fetchArray();
				if (!$_row['count']) {
					// no entry was found so we'll add one
					$multiaccounts[] = $row;

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
				}
			}

			// send private messages to admins
			$recipients = $this->getNotificationUserList();

			if (count($multiaccounts) && count($recipients)) {
				$accountlist = "[list]";

				foreach ($multiaccounts as $m) {
					$accountlist .= "[*] ".$m['username'];
				}

				$accountlist .= "[/list]";

				foreach ($recipients as $recipient) {
					$sql = "SELECT username FROM wcf".WCF_N."_user
							WHERE userID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute(array($recipient));

					$row = $statement->fetchArray();
					$username = $row['username'];

					$message = WCF::getLanguage()->getDynamicVariable('wcf.iplog.messages.newMultiAccount.text', array(
						'admin' => $username,
						'username' => WCF::getUser()->username,
						'ip' => UserUtil::convertIPv6To4(UserUtil::getIpAddress()),
						'accounts' => $accountlist,
					));

				    $conversationAction = new ConversationAction(array(), 'create', array(
				        'data' => array(
				            'subject' => WCF::getLanguage()->getDynamicVariable('wcf.iplog.messages.newMultiAccount.title', array()),
				            'time' => TIME_NOW,
				            'userID' => $recipient,
				            'username' => $username
				        ),
				        'messageData' => array(
				            'message' => $message
				        ),
				        'participants' => array($recipient)
				    ));

				    $returnValues = $conversationAction->executeAction();
				    MessageQuoteManager::getInstance()->saved();
				}
			}
		}

		return 1;
	}

	/**
	 * receive list of users who will receive a notification
	 */
	public function getNotificationUserList() {
		$userIDs = array();
		$groups = array();

		// fetch the option id of $this->iplogReceiveInfoOption
		$sql = "SELECT optionID FROM wcf".WCF_N."_user_group_option
				WHERE optionName = ?;";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->iplogReceiveInfoOption));

		$row = $statement->fetchArray();
		$optionID = $row['optionID'];

		// get all groups which should receive a notification
		$sql = "SELECT groupID FROM wcf".WCF_N."_user_group_option_value
				WHERE
					optionID = ? AND
					optionValue = 1";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($optionID));

		while ($row = $statement->fetchArray()) {
			$groups[] = $row['groupID'];
		}

		// get all users who are in at least one of the groups $groups
		if (!count($groups)) {
			return array();
		}

		foreach ($groups as $group) {
			$sql = "SELECT userID FROM wcf".WCF_N."_user_to_group
					WHERE groupID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($group));

			while ($row = $statement->fetchArray()) {
				$userIDs[] = $row['userID'];
			}
		}

		return array_unique($userIDs);
	}
 }