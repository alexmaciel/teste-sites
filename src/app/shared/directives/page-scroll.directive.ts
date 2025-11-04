import { AfterViewInit, Directive, ElementRef, Inject, NgZone, OnDestroy, PLATFORM_ID, Renderer2 } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

//import { ResizeObserver } from '@juggle/resize-observer';

// typical import
import gsap from "gsap";

import { ScrollTrigger } from "gsap/ScrollTrigger";
import { ScrollSmoother } from "gsap/ScrollSmoother";
import { ScrollToPlugin } from "gsap/ScrollToPlugin";
import { SplitText } from "gsap/SplitText";

export const instances = {
  scroll: undefined,
  slider: undefined,
};

@Directive({
  selector: '[data-page-scrolling]',
})
export class PageScrollDirective implements AfterViewInit, OnDestroy {
  
  private getEl!: ElementRef | null;
  private getSmoother: HTMLElement | any | null;

  private getItems: any[] = [];
  private getImage: HTMLElement[] | undefined; 
  private getText: HTMLElement | any; 
  private getImageInner: HTMLElement | any | null; 
  
  private getPage!: HTMLElement | null; 
  private getWrapper!: HTMLElement | null; 

  private getHeader!: HTMLElement | null; 
  private getFooter!: HTMLElement | null; 

  private getLinks: HTMLElement | any | null; 

  private getSplits!: NodeListOf<Element>;
  private getAnims: gsap.core.Tween[] = [];
  private getSplitsInstances: SplitText[] = [];

  deltaY = 0;

  private _isBrowser = false;

  constructor(
    @Inject(PLATFORM_ID) private platformId: object,
    private zone: NgZone,    
    private readonly el: ElementRef,
    private readonly renderer: Renderer2,    
  ) {
    this._isBrowser = isPlatformBrowser(this.platformId);
    // import gsap from "gsap"
    gsap.registerPlugin(ScrollTrigger, ScrollSmoother, SplitText, ScrollToPlugin);  
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
      this.init();
    });
  }

  ngOnDestroy(): void {
    if (this._isBrowser) {
      this.getSmoother?.kill();
      this.getAnims.forEach(anim => anim.kill());
      this.getSplitsInstances.forEach(split => split.revert());

      window.removeEventListener('resize', this.resizeHandler);
    }
  }

  init() {
    console.clear();
    gsap.config({ nullTargetWarn: false });

    if (typeof document !== 'undefined') {


      this.getPage          = document.getElementById('app-page');
      this.getWrapper       = document.getElementById('app-wrapper');

      this.getHeader        = document.getElementById('app-header');
      this.getFooter        = document.getElementById('app-footer');

      this.getFooter        = document.getElementById('app-footer');

      this.getSplits        = document.querySelectorAll('#splits');
    }
    
    // @ts-ignore
    const scroll = this.getEl?.nativeElement.getBoundingClientRect();

    /*
    const ro = new ResizeObserver((entries, observer) => {
      entries.forEach((entry, index) => {
        const { inlineSize: width, blockSize: height } = entry.contentBoxSize[0];
        if(this.getSmoother) {
          setTimeout(() => {
            this.animateOnScroll();
          }, 1);          
        }
      });
    });
    ro.observe(this.getEl);
    */

    /*
    ScrollTrigger.scrollerProxy(this.getEl?.nativeElement, {
      scrollTop(value) {
        return arguments.length ?
          scroll.scrollTo(value, 0, 0) :
          scroll.scroll.instance.scroll.y
      },
      getBoundingClientRect() {
        return {
          left: 0, top: 0,
          width: window.innerWidth,
          height: window.innerHeight
        }        
      }
    });
    ScrollTrigger.refresh(); 
    ScrollTrigger.addEventListener('refresh', () => ScrollTrigger.update());
    */
   
    window.addEventListener("scroll", () => ScrollTrigger.update(), true); 
    window.addEventListener("resize", () => ScrollTrigger.update(), true);   
    window.addEventListener('resize', this.resizeHandler);

    this.animateOnScroll();
  }

  // ScrollTrigger animations for scrolling
  private animateOnScroll(): void {
    // Get the current ScrollSmoother instance and 'reset' it
    const previouslyCreatedSmoother = ScrollSmoother.get();  
    previouslyCreatedSmoother?.scrollTo(0);
    previouslyCreatedSmoother?.kill();

    // create the smooth scroller
    if (ScrollTrigger.isTouch === 0) {
      this.getSmoother = ScrollSmoother.create({
        wrapper: this.getPage,
        content: this.getWrapper,
        smooth: 1.2,
        effects: true,
        //normalizeScroll: true,
        smoothTouch: 0.1,
        onUpdate: (self) => {
          let scrollPosition = 0,
              height: number | any = 0;

          scrollPosition = self.scrollTop();
          height = document.querySelector('.app-header')?.getBoundingClientRect().height;

          if(scrollPosition > height) {
            document.body.setAttribute('data-mv-app-header-sticky', 'true');
          }  else if (scrollPosition < height) {
            document.body.removeAttribute('data-mv-app-header-sticky');
          }   
        }
      });      

      this.getSmoother.scrollTo("", false, "center center");
      this.initSplits();
      this.initFooter();
    }
  }

  initSplits(): void {
    this.getSplits.forEach((el: Element) => {
      // const el = splitRef.nativeElement as HTMLElement;
      // Criar SplitText
      const splitInstance = new SplitText(el, {
        type: 'lines,words,chars',
        linesClass: 'split-line',
        wordsClass: 'split-word'
      });
      this.getSplitsInstances.push(splitInstance);
      // Criar animação GSAP
      const anim = gsap.from(splitInstance.chars, {
        scrollTrigger: {
          trigger: el,
          toggleActions: 'restart pause resume reverse',
        },
        duration: 0.25,
        delay: 0.25,
        yPercent: 100,
        rotateZ: '6deg',
        opacity: 0,
        stagger: 0.01
      });

      this.getAnims.push(anim);
    });
  }

  initFooter() {
    const footerInner = this.getFooter?.querySelector('.app-footer-content') as HTMLElement;
    const tl = gsap.timeline({
      scrollTrigger: {
        trigger: this.getWrapper,
        scrub: 1,
        start: "center bottom+=25%",
        end: "bottom bottom",
      },
      defaults: {
        ease: "none"
      }
    });   
    tl.add("start")
      .fromTo(footerInner, {
        opacity: 0,
        yPercent: -50,
      }, {
        opacity: 1,
        yPercent: 0,
        ease: "none",
    }, "start")     
  }

  private resizeHandler = this.onResize.bind(this);

  onResize(): void {
    ScrollTrigger.refresh();
  }

	// Throttle function: Input as function which needs to be throttled and delay is the time interval in milliseconds
	throttle(timer: number | undefined, func: Function, delay?: number) {
		// If setTimeout is already scheduled, no need to do anything
		if (timer) {
			return
		}
	
		// Schedule a setTimeout after delay seconds
		timer = window.setTimeout(function () {
			func()
			// Once setTimeout function execution is finished, timerId = undefined so that in <br>
			// the next scroll event function execution can be scheduled by the setTimeout
			timer = undefined
		}, delay)
	}	   
}
