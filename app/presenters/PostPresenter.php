<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class PostPresenter extends BasePresenter
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function renderShow($postId)
    {
        $post = $this->database->table('posts')->get($postId);
        if(!$post)
        {
            $this->error("Stránka nebyla nalezena!");
        }
        $this->template->post = $post;
        $this->template->comments = $post->related('comments')->order('created_at');
    }

    protected function createComponentCommentForm()
    {
        $form = new Form;

        $form->addText('name', 'Jméno:')
            ->setRequired();

        $form->addText('email', 'Email:');

        $form->addTextArea('content', 'Komentář')
            ->setRequired();

        $form->addSubmit('send', 'Publikovat komentář');

        $form->onSuccess[] = array($this, 'commentFormSucceeded');

        return $form;
    }

    public function commentFormSucceeded($form)
    {
        $values = $form->values;

        $postId = $this->getParameter('postId');

        $this->database->table('comments')->insert(array(
            'post_id' => $postId,
            'name' => $values->name,
            'email' => $values->email,
            'content' => $values->content,
        ));
        $this->flashMessage("Děkuji za komentář", 'success');
        $this->redirect('this');
    }

    protected function createComponentPostForm()
    {
        $form = new Form;

        $form->addText('title', 'Titulek:')
            ->setRequired();

        $form->addTextArea('content', 'Obsah článku:')
            ->setRequired();

        $form->addSubmit('send', 'Uložit článek');

        $form->onSuccess[] = array($this, 'postFormSucceeded');

        return $form;
    }

    public function postFormSucceeded($form)
    {
        if(!$this->getUser()->isLoggedIn())
        {
            $this->error('Pro vytvoření nebo editování článku se musíte přihlásit!');
        }

        $values = $form->values;

        $postId = $this->getParameter('postId');
        if($postId)
        {
            $post = $this->database->table('posts')->get($postId);
            $post->update($values);
        }
        else
        {
            $post = $this->database->table('posts')->insert($values);
        }

        $this->flashMessage('Článek byl úspěšně publikován.', 'success');
        $this->redirect('show', $post->id);
    }

    public function actionEdit($postId)
    {
        if(!$this->getUser()->isLoggedIn())
        {
            $this->redirect('Sign:in');
        }

        $post = $this->database->table('posts')->get($postId);
        if(!$post)
        {
            $this->error('Článek nebyl nalezen!');
        }
        $this['postForm']->setDefaults($post->toArray());
    }

    public function actionCreate()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
}