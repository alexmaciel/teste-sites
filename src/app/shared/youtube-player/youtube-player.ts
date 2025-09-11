import {
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ElementRef,
  Input,
  NgZone,
  OnChanges,
  OnDestroy,
  PLATFORM_ID,
  SimpleChanges,
  ViewChild,
  ViewEncapsulation,
  Inject,
  AfterViewInit,
  Output,
  EventEmitter,
} from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
import { PlaceholderImageQuality } from '@angular/youtube-player';

interface Video {
  id: string;
  name: string;
  isPlaylist?: boolean;
  autoplay?: boolean;
  placeholderQuality: PlaceholderImageQuality;
}

@Component({
  selector: 'ngx-youtube-player',
  changeDetection: ChangeDetectionStrategy.OnPush,
  encapsulation: ViewEncapsulation.None,
  template: `
    <div #ngxYouTubePlayer class="yt-wrapper" role="region" aria-label="Player de vídeo do YouTube">
      <youtube-player
        [videoId]="videoId"
        [playerVars]="_playerVars"
        [startSeconds]="startAt30s ? 30 : 0"
        [width]="videoWidth"
        [height]="videoHeight"
        [disableCookies]="disableCookies"
        [disablePlaceholder]="disablePlaceholder"
        [placeholderImageQuality]="placeholderQuality"        
        (ready)="onReady($event)"
        (stateChange)="onStateChange($event)"
        (error)="onError($event)">
      </youtube-player>
    </div>
  `
})
export class NgxYouTubePlayer implements AfterViewInit, OnChanges, OnDestroy {
    @Input() videoId?: string;
    @Input() playlistId?: string;
    @Input() playerVars?: YT.PlayerVars;

    @Output() ready = new EventEmitter<YT.PlayerEvent>();
    @Output() stateChange = new EventEmitter<YT.OnStateChangeEvent>();
    @Output() error = new EventEmitter<YT.OnErrorEvent>();

    @ViewChild('ngxYouTubePlayer', { static: true }) ngxYouTubePlayer!: ElementRef<HTMLDivElement>;

    private _player?: YT.Player;
    private _isBrowser = false;

    // Vars aplicadas de fora + do selectedVideo
    _playerVars: YT.PlayerVars = {
        rel: 0,
        modestbranding: 1,
        color: 'white',
        controls: 1,
        autoplay: 0,
        cc_load_policy: 0
    };

    videoWidth = 0;
    videoHeight = 0;

    disableCookies = false;
    disablePlaceholder = false;
    startAt30s = false;
    placeholderQuality: PlaceholderImageQuality = 'standard';

    constructor(
        private _cdr: ChangeDetectorRef,
        @Inject(PLATFORM_ID) private platformId: object,
        private zone: NgZone
    ) {
        this._isBrowser = isPlatformBrowser(this.platformId);
    }

    set selectedVideo(value: Video | undefined) {
    if (!value) return;
    this.placeholderQuality = value.placeholderQuality || 'standard';

    this._playerVars = value.isPlaylist
        ? { listType: 'playlist', list: value.id, autoplay: value.autoplay ? 1 : 0 }
        : { autoplay: value.autoplay ? 1 : 0, rel: 0, modestbranding: 1 };
    }

    // Run the function only in the browser
    browserOnly(f: () => void) {
        if (this._isBrowser) {
            this.zone.runOutsideAngular(() => {
                f();
            });
        }
    }    

    ngAfterViewInit(): void {
        this.browserOnly(() => {
            this.resizeHandler();
            window.addEventListener('resize', this.resizeHandler);
        });
    }

    ngOnChanges(changes: SimpleChanges): void {
        this.updatePlayerVars();
        if (this.shouldRecreatePlayer(changes)) {
            // TODO: lógica para recriar player (se necessário)
            this._cdr.markForCheck();
        }
    }

    private resizeHandler = this.onResize.bind(this);

    private updatePlayerVars() {
        if (this.playlistId) {
            this._playerVars = {
                ...this._playerVars,
                listType: 'playlist',
                list: this.playlistId,
                autoplay: this.playerVars?.autoplay ?? 0
            };
        } else if (this.videoId) {
            this._playerVars = {
                ...this._playerVars,
                autoplay: this.playerVars?.autoplay ?? 0
            };
        }
    }  

    private requestFullscreen() {
        const elem = this.ngxYouTubePlayer.nativeElement;
        if (elem.requestFullscreen) {
            elem.requestFullscreen();
        } else if ((elem as any).webkitRequestFullscreen) {
            (elem as any).webkitRequestFullscreen();
        } else if ((elem as any).msRequestFullscreen) {
            (elem as any).msRequestFullscreen();
        }
    }    

    private shouldRecreatePlayer(changes: SimpleChanges): boolean {
        return !!(
            changes['videoId'] ||
            changes['playlistId'] ||
            changes['playerVars'] ||
            changes['disableCookies'] ||
            changes['disablePlaceholder']
        ) && !changes['videoId']?.isFirstChange();  
    }

    /**
     * 
     */
    onResize(): void {
        if (this._isBrowser) {
            const size = document.body.getBoundingClientRect();
            this.videoWidth = Math.min(this.ngxYouTubePlayer.nativeElement.clientWidth, size.width);
            this.videoHeight = size.height - 100;
            this._cdr.markForCheck();
        }
    };

    /**
     * 
     * @param ready event
     */
    onReady(e: YT.PlayerEvent) {
        this._player = e.target;
        this.ready.emit(e);
    }

    /**
     * 
     * @param change event 
     */
    onStateChange(e: YT.OnStateChangeEvent) {
        this.stateChange.emit(e);
    }

    /**
     * 
     * @param error event
     */
    onError(e: YT.OnErrorEvent) {
        this.error.emit(e);
    }

    ngOnDestroy(): void {
        if (this._isBrowser) {
            this._player?.destroy();
            window.addEventListener('resize', this.resizeHandler);
        }
    }
}
