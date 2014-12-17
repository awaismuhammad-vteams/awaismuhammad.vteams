<?php
//print_r($profile);exit;
?>

<div class="" id="calendar_page">
    <div class="span9">
        <?php include_once './themes/hebo/views/layouts/tpl_nav_for_loggedin.php'; ?>

        <div class="tab-content" style="margin-top: 1px;">
            <?php include_once './themes/hebo/views/layouts/tpl_nav_for_user_articles.php'; ?>

            <h3 style="margin: 0;">My Personal Articles</h3>

            <div class="messages"></div>

            <div class="row-fluid">
                <!-- CONTENT FOR INDIVIDUAL PAGES GOES HERE.  TAKE THIS CODE AND STICK IT WHERE IT NEEDS TO GO -->

                <?php
                    $myArticles = Article::model()->findAllByAttributes(array('user_id'=>Yii::app()->user->id));
                    //$myArticles = Article::model()->findAllByAttributes(array('created_by'=>'1'));
                    //print_r($myArticles);//die();
                    if(count($myArticles)>0){
                    $i=0;

                    foreach($myArticles AS $index=>$article)
                    {
                        //print_r($article);die();
                         //$left_none = "left-none";
                ?>
                <div id="<?php echo $article['id']; ?>" class="main-box span6 <?php echo($i%2==0)?'left-none':'' ; ?>" style="height: 275px;">

                    <!--<div class="recipe-box span8" style="background-image: url(<?php echo $this->assetsurl; ?>/recipe-images/default.jpg); width: 100%; height: 100px; background-repeat: no-repeat; background-size: 100% 100%; margin-bottom: 0px;">-->
                    <a href="<?php echo Yii::app()->homeUrl . '/article/'; ?><?php echo $article['id']; ?>">
                        <div class="recipe-box span8" style="background-image: url(<?php echo Article::model()->getImage($article['image']); ?>); width: 100%; height: 200px; background-repeat: no-repeat; background-size: 100% 100%; margin-bottom: 5px;">

                            <?php if($article['status']==1){ ?>
                            <img src="<?php echo Yii::app()->theme->baseUrl;?>/img/accepted.png" title="Approved">
                            <?php }else if($article['status']==2){ ?>
                            <img src="<?php echo Yii::app()->theme->baseUrl;?>/img/denied.png" title="Rejected">
                            <?php }else{ ?>
                            <img src="<?php echo Yii::app()->theme->baseUrl;?>/img/pending.png" title="Pending for Approval">
                            <?php } ?>


                        </div>
                    </a>

                    <div class="span8">
                        <span style="font-size: 16px; font-weight: bold;"><?php echo $article['title']; ?></span>
                        <br>
                        Category: <?php echo $article->category->name; ?>
                        <br>
                        <!--By: <a href="<?php echo Yii::app()->homeUrl . '/recipe/'; ?><?php echo $article['id']; ?>"><?php echo $article->user->profile->firstname.' '.$article->user->profile->lastname; ?></a>-->
                        By: <?php echo $article->user->profile->firstname.' '.$article->user->profile->lastname; ?>
                    </div>
                    <div class="span4" style="min-height: 20px; margin-left: 0px; float: right; margin-top: 2px; padding-left: 18px;">
                        <?php
                        $this->widget('CStarRating', array(
                            'name' => 'recipe-total-rating'.$i,
                            'value' => ArticleRating::model()->getArticleTotalRating($article['id']),
                            'minRating' => 1,
                            'maxRating' => 5,
                            'readOnly' => true,
                        ));
                        ?>
                        <br>
                        <span class="icon-trash" title="Delete Article" style="cursor:pointer; float: right; margin-left: 5px; margin-right: 5px;" onclick="javascript: deleteMyArticle(<?php echo $article['id']; ?>);"></span>

                        <a href="<?php echo Yii::app()->createUrl('article/update/'.$article['id']); ?>">
                            <span class="icon-edit" title="Update Article" style="cursor:pointer; float: right;"></span>
                        </a>
                    </div>
                    <!--<div class="span11" style="vertical-align: top; padding-left: 2px; padding-right: 2px; float: left; text-align: left;">
                        <?php //echo $article['text']; ?>
                    </div>-->
                    

                </div>
                <?php
                            $i++;
                        }
                    }

                    else{
                        echo 'You have not created any Article.';
                    }
                ?>

                <!-- END INDIVIDUAL PAGE CONTENT -->
            </div>

        </div>

    </div>

</div>

<script type="text/javascript">
    var err_msg = '<div class="alert alert-error"><button data-dismiss="alert" class="close" type="button">x</button><strong></strong> message</div>';
    var success_msg = '<div class="alert alert-success"><button data-dismiss="alert" class="close" type="button">x</button><strong></strong> message</div>';

    function deleteMyArticle(articleId)
    {
        //alert("JS deleteMyArticle Called: "+articleId);
        dataString = "&articleId=" + articleId;
        $.ajax({
                    
                    url: "<?php echo Yii::app()->createUrl("article/deletemyarticle"); ?>/",
                    type: 'POST',
                    data: dataString,
                    success: function(data) {
                        //alert(data)
                        if (data) {
                            $('#'+articleId).html('This Article deleted successfully!');
                            //$('#'+recipeId).hide();
                            $("div.messages").html(success_msg.replace("message", "Article deleted successfully!")).slideDown(500);
                        }
                        else{
                            $("div.messages").html(err_msg.replace("message", "Article, didn't deleted. Please try again!")).slideDown(500);

                        } 
                    }
        })
    }

    $(document).ready(function() {
        
    });
</script>