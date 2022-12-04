<?php

namespace App\Presenters;

use App\Model\Facades\TagsFacade;
use App\Model\Facades\TodosFacade;
use App\Components\TodoEditForm\TodoEditFormFactory;
use App\Components\TodoEditForm\TodoEditForm;
use Nette\Application\UI\Presenter;
use Nette\Utils\Paginator;

/**
 * Class TodoPresenter
 * @package App\Presenters
 */
class TodoPresenter extends Presenter{
  private TodosFacade $todosFacade;
  private TagsFacade $tagsFacade;

  private TodoEditFormFactory $todoEditFormFactory;
  /** @persistent */
  public int $tagId;

  /** @persistent */
  public $page = 1;

  /**
   * Akce pro výpis úkolů
   */
  public function actionDefault():void {
    $tag = null;

    if (!empty($this->tagId)) {
      try {
        $tag = $this->tagsFacade->getTag($this->tagId);
      } catch (\Exception $e) {
        $this->flashMessage('Tag nebyl nalezen', 'danger');
        $this->redirect('this', ['tagId' => null]);
      }
    }
    $this->template->currentTag = $tag;
    $this->template->tags = $this->tagsFacade->findTags();

    $paginator = new Paginator();
    $paginator->setItemCount($this->todosFacade->findCountByTagAndState($tag, null));
    $paginator->setItemsPerPage(3);

    $currentPage = min($this->page, $paginator->pageCount);
    $currentPage = max ($currentPage, 1);
    if ($currentPage != $this->page) {
      $this->redirect('default', ['page' => $currentPage]);
    }

    $paginator->setPage($currentPage);

    $this->template->todos=$this->todosFacade->findTodosByTagAndState($tag, null, $paginator->offset, $paginator->itemsPerPage);
    $this->template->paginator = $paginator;
  }

  public function actionEdit(int $id): void {
    try {
      $todo = $this->todosFacade->getTodo($id);
    } catch (\Exception $e) {
      $this->flashMessage('Úkol nebyl nalezen', 'danger');
      $this->redirect('default');
    }

    $this->getComponent('todoEditForm')->setDefaults($todo);
  }

  protected function createComponentTodoEditForm():TodoEditForm {
    $todoEditForm = $this->todoEditFormFactory->create();
    $todoEditForm->onFinished[] = function () {
      $this->flashMessage('Úkol byl úspěšně uložen.', 'success');
      $this->redirect('default');
    };
    $todoEditForm->onFailed[] = function (string $message) {
      $this->flashMessage($message, 'danger');
      $this->redirect('this');
    };
    $todoEditForm->onCancel[] = function () {
      $this->redirect('default');
    };
    return $todoEditForm;
  }

  public function handleCompleted(int $id, bool $completed) {
    try {
      $todo = $this->todosFacade->getTodo($id);
    } catch (\Exception $e) {
      $this->flashMessage('Úkol nebyl nalezen', 'danger');
      $this->redirect('this');
    }

    if ($todo->completed !== $completed) {
      $todo->completed = $completed;
      $this->todosFacade->saveTodo($todo);

      $this->flashMessage('Stav úkolu byl změněn.', 'success');
      $this->redirect('this');
    }
  }

  #region injections
  public function injectTodosFacade(TodosFacade $todosFacade):void {
    $this->todosFacade=$todosFacade;
  }

  public function injectTagsFacade(TagsFacade $tagsFacade):void {
    $this->tagsFacade=$tagsFacade;
  }

  public function injectTodoEditFormFactory(TodoEditFormFactory $todoEditFormFactory):void {
    $this->todoEditFormFactory=$todoEditFormFactory;
  }
  #endregion injections
}