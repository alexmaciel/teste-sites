import { isPlatformBrowser } from '@angular/common';
import { afterNextRender, AfterViewInit, ChangeDetectionStrategy, Component, ContentChild, ElementRef, Inject, Input, NgZone, PLATFORM_ID, ViewEncapsulation } from '@angular/core';
import { SwiperContainer } from 'swiper/element/bundle';
import { SwiperOptions } from 'swiper/types';


@Component({
  selector: 'ngx-swiper',
  templateUrl: './swiper.component.html',
  styleUrl: './swiper.component.scss',
  host: { ngSkipHydration: 'true' },
  changeDetection: ChangeDetectionStrategy.OnPush,
  encapsulation: ViewEncapsulation.None,
})
export class SwiperComponent implements AfterViewInit {
  @ContentChild('swiper') swiperRef!: ElementRef<SwiperContainer>;

  @Input() swiperContainerId = '';
  @Input() config?: SwiperOptions;
  
  index = 0;
  slidePerView = 1;
  initialized = false;

  private _isBrowser = false;

  constructor(
    private zone: NgZone,
    @Inject(PLATFORM_ID) private platformId: object,
  ) { 
    this._isBrowser = isPlatformBrowser(this.platformId);
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
      Object.assign(this.swiperRef.nativeElement, this.config);
      //@ts-ignore - We ignore this because there is no initialize method on the HTMLElement
      this.swiperRef.nativeElement?.initialize(); 

      if (typeof document !== 'undefined') {
        document.getElementById(this.swiperContainerId)
        ?.getElementsByClassName('slider')[0]?.shadowRoot
        ?.firstChild as HTMLElement;
      }
    });
  }

  changeSlide(prevOrNext: number): void {
    if (prevOrNext === -1) {
      this.swiperRef.nativeElement.swiper.slidePrev();
    } else {
      this.swiperRef.nativeElement.swiper.slideNext();
    }
  }

}
