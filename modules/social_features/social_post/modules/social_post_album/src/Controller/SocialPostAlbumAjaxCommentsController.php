<?php

namespace Drupal\social_post_album\Controller;

use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityInterface;
use Drupal\social_ajax_comments\Controller\AjaxCommentsController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller routines for AJAX comments routes.
 */
class SocialPostAlbumAjaxCommentsController extends AjaxCommentsController {

  /**
   * {@inheritdoc}
   */
  public function socialAdd(Request $request, EntityInterface $entity, $field_name, $pid = NULL) {
    $this->clearTempStore = FALSE;

    $response = parent::socialAdd($request, $entity, $field_name, $pid);

    if ($this->errors !== 0) {
      if ($this->errors !== NULL) {
        $this->tempStore->deleteAll();
      }

      return $response;
    }

    $comment = $this->entityTypeManager()->getStorage('comment')->create([
      'entity_id' => $entity->id(),
      'pid' => $pid,
      'entity_type' => $entity->getEntityTypeId(),
      'field_name' => $field_name,
    ]);

    $form = $this->entityFormBuilder()->getForm($comment);
    $this->tempStore->setSelector('form_html_id', $form['#attributes']['id']);

    $response->addCommand(new ReplaceCommand(
      $this->tempStore->getSelectors($request)['wrapper_html_id'] . '-modal',
      $this->renderCommentField($entity, $field_name)
    ), TRUE);

    $this->tempStore->deleteAll();

    return $response;
  }

}
