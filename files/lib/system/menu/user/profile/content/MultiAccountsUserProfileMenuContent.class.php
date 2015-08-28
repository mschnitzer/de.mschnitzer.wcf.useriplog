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
class MultiAccountsUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent {
        
        /**
         * The maximum of listed ip addresses
         */
        public $maxIpAddresses = 5;
        
        /**
         * The user ip address list with $maxIpAddresses entries
         */
        public $ipAddressList = array();
        
        /**
         * @see \wcf\system\menu\user\profile\content\IUserProfileMenuContent::getContent()
         */
        public function getContent($userID) {
            $sql = "SELECT * FROM wcf".WCF_N."_user_iplog
                    WHERE
                            userID = ?
                    ORDER BY entryID ASC
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
            
            WCF::getTPL()->assign(array(
                'ipAddressList' => $this->ipAddressList,
                'maxIpAddresses' => $this->maxIpAddresses,
                'userID' => $userID,
            ));
    
            return WCF::getTPL()->fetch('userProfileMultiAccounts');
        }
    
        /**
         * @see \wcf\system\menu\user\profile\content\IUserProfileMenuContent::isVisible()
         */
        public function isVisible($userID) {
            return true;
        }
}
