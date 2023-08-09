<?php

  namespace Lassi\Server\Commands;

  use Illuminate\Console\Command;
  use App\Models\User;

  class LassiTestCommand extends Command
  {
    protected $signature = 'lassi:test {--count=} {--modify} {--create}';

    protected $description = 'Test command to modify and create users';

    public function handle(): void
    {
        $count = $this->option('count',0);
        if ($count > 0){
            if ($this->option('modify')){
              $users =  User::where('id','>',rand(4,18000))->take($count)->get();
              $users->each(function ($u){
                  $u->touch();
                  $u->save();
              });
            }

            if($this->option('create')){
                User::Factory()->Count($count)->create();
            }

        }

    }
  }
