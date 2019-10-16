<?php
/**
 * @package      Crowdfunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="cfbacking<?php echo $this->params->get('pageclass_sfx'); ?>">
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>
	
	<div class="row">
		<div class="col-md-12">
    		<?php
				$layout      = new JLayoutFile('payment_wizard');
        	  	echo $layout->render($this->layoutData);
    		?>	
    	</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo JText::_('COM_CROWDFUNDING_ENTER_AN_AMOUNT');?></div>
                <div class="panel-body">
                    <form method="post" action="<?php echo JRoute::_(CrowdfundingHelperRoute::getBackingRoute($this->item->slug, $this->item->catslug));?>" class="mt-0" id="form-pledge" autocomplete="off">
    				<?php echo JHtml::_('crowdfunding.inputAmount', $this->rewardAmount, array('name'=>'amount', 'id'=>'js-current-amount'), $this->moneyFormatter, $this->currency); ?>
					<?php echo JHtml::_('crowdfunding.minMaxAllowedAmount', $this->params->get('backing_minimum_amount'), $this->params->get('backing_maximum_amount'), $this->moneyFormatter, $this->moneyParser, $this->currency); ?>
    				<?php if($this->params->get('backing_terms', 0)) {
    				    $termsUrl = $this->params->get('backing_terms_url', '');
    				?>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="terms" value="1">&nbsp;
                            <?php echo (!$termsUrl) ? JText::_('COM_CROWDFUNDING_TERMS_AGREEMENT') : JText::sprintf('COM_CROWDFUNDING_TERMS_AGREEMENT_URL', $termsUrl);?>
                        </label>
                    </div>
                    <?php }?>
    				<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
    				<input type="hidden" name="rid" value="<?php echo $this->rewardId; ?>" id="js-reward-id" />
    				<input type="hidden" name="task" value="<?php echo $this->nextStepTask; ?>" />
    				<input type="hidden" name="layout" value="<?php echo $this->nextStepLayout; ?>" />
    				<?php echo JHtml::_('form.token'); ?>
    				
    				<button type="submit" class="btn btn-primary" <?php echo $this->disabledButton;?>>
						<span class="fa fa-check"></span>
                        <?php echo JText::_('COM_CROWDFUNDING_SUBMIT_CONTINUE');?>
                    </button>
                    </form>
                </div>
            </div>

			<?php if($this->rewardsEnabled) {?>
			<div class="cfrewards">
			    <div class="reward_title pull-center"><?php echo JText::_('COM_CROWDFUNDING_REWARDS');?></div>
			
            	<div class="reward">
            		<a href="javascript: void(0);" class="js-reward-amount" >
            			<span class="ramount">
            			<input type="radio" name="reward" value="0" data-id="0" class="js-reward-amount-radio" <?php echo (!$this->rewardId) ? 'checked="checked"' : ''; ?> />
            			<?php echo JText::_('COM_CROWDFUNDING_NO_REWARD'); ?>
            			</span>
            			<span class="rdesc"><?php echo JText::_('COM_CROWDFUNDING_JUST_INVEST'); ?></span>
            		</a>
            	</div>
            	<?php foreach($this->rewards as $reward) {?>
            	<div class="reward">
            		<a href="javascript: void(0);" class="js-reward-amount" >
            			<span class="ramount">
            			<input type="radio" name="reward" value="<?php echo $reward['amount'];?>" data-id="<?php echo $reward['id'];?>" class="js-reward-amount-radio" <?php echo ((int)$this->rewardId !== (int)$reward['id']) ? '' : 'checked="checked"'?>/>
            			<?php 
            			$amount = $this->moneyFormatter->formatCurrency(new Prism\Money\Money($reward['amount'], $this->currency));
            			echo JText::sprintf('COM_CROWDFUNDING_INVEST_MORE', $amount ); ?>
            			</span>
            			<span class="rtitle"><?php echo $this->escape($reward['title']); ?></span>
            			<span class="rdesc"><?php echo $this->escape($reward['description']); ?></span>
            		</a>
            	</div>
            	<?php }?>
            </div>
            <?php } ?>
    	</div>
    	
	</div>
</div>