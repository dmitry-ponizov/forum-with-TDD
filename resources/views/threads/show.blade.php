@extends('layouts.app')

@section('content')
    <thread-view inline-template :initial-replies-count="{{ $thread->replies_count }}">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="level">
                                <img src="{{ asset($thread->creator->avatar_path) }}" width="25px" height="25px"
                                     style="border-radius: 50%; margin-right: 10px ;" alt="">
                                <span class="flex">
                                 <a href="/profiles/{{$thread->creator->name }}"> {{$thread->creator->name}}</a> posted:
                                    {{$thread->title }}
                            </span>
                                @can('update',$thread)
                                    <form action="{{$thread->path()}}" method="POST">
                                        {{csrf_field()}}
                                        {{method_field('DELETE')}}
                                        <button type="submit" class="btn btn-link">
                                            Delete thread
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            {{$thread->body}}
                        </div>
                    </div>
                    <replies @removed="repliesCount--"
                             @added="repliesCount++"></replies>

                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <p>
                                This thread was published {{$thread->created_at->diffForHumans()}}
                                by <a href="# ">{{ $thread->creator->name }}</a>, and currently
                                has
                                <span v-text="repliesCount"></span> {{ str_plural('comment',$thread->replies_count) }}
                            </p>
                            <p>
                                <subscribe-button
                                        :active="{{ json_encode($thread->isSubscribedTo) }}"></subscribe-button>
                            </p>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </thread-view>
@endsection
