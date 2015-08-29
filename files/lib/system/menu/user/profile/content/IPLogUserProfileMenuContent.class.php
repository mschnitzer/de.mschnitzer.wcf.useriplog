<?php
namespace wcf\system\menu\user\profile\content;
use wcf\data\user\User;
use wcf\system\option\user\UserOptionHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Shows information about the multiaccounts of a user
 *
 * @author      Manuel Schnitzer
 * @copyright   2015-2015
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     com.woltlab.wcf
 * @subpackage  system.menu.user.profile.content
 * @category    Community Framework
 */
class IPLogUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent {
        
        /**
         * The maximum of listed ip addresses
         */
        public $maxIpAddresses = IPLOG_MAX_PROFILE_IP_ENTRIES;
        
        /**
         * The user ip address list with $maxIpAddresses entries
         */
        public $ipAddressList = array();

        /**
         * An array with all multi accounts associated to that account
         */
        public $multiaccounts = array();

        /**
         * If the user should see the menu item, the value should be "true"
         */
        protected $visible = false;
        
        /**
         * @see \wcf\system\menu\user\profile\content\IUserProfileMenuContent::getContent()
         */
        public function getContent($userID) {
            // fetch user name of the profile owner
            $sql = "SELECT username FROM wcf".WCF_N."_user
                    WHERE userID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute(array($userID));

            $row = $statement->fetchArray();
            $username = $row['username'];

            // fetch user ips
            if (WCF::getSession()->getPermission('mod.iplog.canSeeIPHistory')) {
                $sql = "SELECT userID, ipAddress, timestamp FROM wcf".WCF_N."_user_iplog
                        WHERE
                                userID = ?
                        GROUP BY userID, ipAddress, timestamp
                        ORDER BY entryID DESC
                        LIMIT 0,".$this->maxIpAddresses;
        
                $statement = WCF::getDB()->prepareStatement($sql);
                $statement->execute(array($userID));
                
                // save data from database to variable
                $i = 0;
                while ($row = $statement->fetchArray()) {
                    $this->ipAddressList[] = $row;
                    
                    // since wcf2 implemented IPv6 support, ip addresses are always stored as IPv6. So we have
                    // to convert it back.
                    $this->ipAddressList[$i]['ipAddress'] = UserUtil::convertIPv6To4($this->ipAddressList[$i]['ipAddress']);
                    $i++;
                }
            }

            // fetch user multi accounts
            if (WCF::getSession()->getPermission('mod.iplog.canSeeMultiAccounts')) {
                $sql = "SELECT * FROM wcf".WCF_N."_user_iplog_multiaccounts
                        WHERE
                            userID = ? OR
                            multiaccountID = ?";
                $statement = WCF::getDB()->prepareStatement($sql);
                $statement->execute(array($userID, $userID));

                while ($row = $statement->fetchArray()) {
                    // get the multi account id
                    if ($row['userID'] == $userID) {
                        $maID = $row['multiaccountID'];
                    }
                    else {
                        $maID = $row['userID'];
                    }

                    // fetch username of multi account
                    $sql = "SELECT username FROM wcf".WCF_N."_user
                            WHERE userID = ?";
                    $_statement = WCF::getDB()->prepareStatement($sql);
                    $_statement->execute(array($maID));

                    $_row = $_statement->fetchArray();

                    $this->multiaccounts[] = array(
                        'userID' => $maID,
                        'username' => $_row['username'],
                        'ipAddress' => UserUtil::convertIPv6To4($row['ipAddress']),
                        'timestamp' => $row['timestamp'],
                    );
                }
            }
            
            WCF::getTPL()->assign(array(
                'ipAddressList' => $this->ipAddressList,
                'ipAddressEntries' => count($this->ipAddressList),
                'multiaccounts' => $this->multiaccounts,
                'multiaccountEntries' => count($this->multiaccounts),
                'maxIpAddresses' => $this->maxIpAddresses,
                'userID' => $userID,
                'username' => $username
            ));
    
            return WCF::getTPL()->fetch('userProfileIPLog');
        }
    
        /**
         * @see \wcf\system\menu\user\profile\content\IUserProfileMenuContent::isVisible()
         */
        public function isVisible($userID) {
            if (WCF::getSession()->getPermission('mod.iplog.canSeeIPHistory') ||
                WCF::getSession()->getPermission('mod.iplog.canSeeMultiAccounts')) {
                $this->visible = true;
                return $this->visible;
            }

            return $this->visible;
        }
}
