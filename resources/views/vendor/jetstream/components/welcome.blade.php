<div class="p-6 sm:px-20 bg-white border-b border-gray-200">
    <div class="mt-8 text-2xl">
        <b>Welcome to LSA Rank Tracking</b>
    </div>
    <div class="mt-6 text-gray-500">
        Lets track keywords that matter to you so you can see who is getting the <b>most market share.</b>
    </div>
    <div class="mt-6 text-gray-500">
        To better use your 7-day trial, we've added a few buttons to make it easier to get started.
    </div>
</div>

<div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2">
    <div class="p-6 justify-center grid">
        <div class="modal fade" id="centermodal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myCenterModalLabel">Track new keyword</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Enter new keyword in search input</p>
                    </div>
                    <livewire:keyword-search-modal/>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#centermodal" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #6658dd; margin: 0; border-color: #6658dd; border-style: solid;">Track New Keyword</button>
        {{--<a href="#" class="btn-primary" itemprop="url" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #6658dd; margin: 0; border-color: #6658dd; border-style: solid; border-width: 8px 16px;">--}}
            {{--Track New Keyword--}}
        {{--</a>--}}
    </div>

    <div class="p-6 border-t border-gray-200 md:border-t-0 md:border-l justify-center grid">
        <a href="https://community.askfortransparency.com/spaces/10526758/content" class="btn-primary" itemprop="url" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #6658dd; margin: 0; border-color: #6658dd; border-style: solid; border-width: 8px 16px;">
            Tutorial Videos
        </a>
    </div>

    <div class="p-6 border-t border-gray-200 justify-center grid">
        <a href="https://community.askfortransparency.com/spaces/10526764/chat" class="btn-primary" itemprop="url" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #6658dd; margin: 0; border-color: #6658dd; border-style: solid; border-width: 8px 16px;">
            Community Forum
        </a>
    </div>

    <div class="p-6 border-t border-gray-200 md:border-l justify-center grid">
        <a href="https://calendly.com/askfortransparency" class="btn-primary" itemprop="url" style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; color: #FFF; text-decoration: none; line-height: 2em; font-weight: bold; text-align: center; cursor: pointer; display: inline-block; border-radius: 5px; text-transform: capitalize; background-color: #6658dd; margin: 0; border-color: #6658dd; border-style: solid; border-width: 8px 16px;">
            LSA Consulting
        </a>
    </div>
</div>
<livewire:timezone-updater/>
