import {
  Directive,
  HostListener,
  Renderer2,
  ElementRef,
  OnDestroy,
  OnInit,
  Input,
} from '@angular/core';
import { NavigationCancel, NavigationEnd, Router } from '@angular/router';
import { Subscription } from 'rxjs';

import gsap from 'gsap';

@Directive({
  selector: '[data-toggle]',
})
export class ToggleMenuDirective implements OnInit, OnDestroy {
  private element!: HTMLElement;

  private aside!: HTMLElement;
  private asideMenu!: HTMLElement;
  private asideContent!: HTMLElement;
  private asideGrid!: HTMLElement;
  private asideBackdrop!: HTMLElement;

  private clicked = false;
  private subscriptions: Subscription[] = [];
  private unlisteners: (() => void)[] = [];

  private startX = 0;
  private currentX = 0;
  private isDragging = false;
    
  /** Permite customizar os seletores dos elementos */
  @Input() asideSelector = '.app-aside';
  @Input() asideMenuSelector = '.app-aside-menu';
  @Input() asideContentSelector = '.app-aside-content';
  @Input() asideGridSelector = '.app-aside-grid';
  @Input() asideBackdropSelector = '.app-aside-backdrop';
  // Close
  @Input() closeBtnSelector = '[data-close]'; private closeBtn?: HTMLElement;  

  constructor(
    private el: ElementRef,
    private renderer: Renderer2,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.element = this.el.nativeElement;

    if (typeof document !== 'undefined') {
      this.aside = document.querySelector(this.asideSelector) as HTMLElement;
      this.asideMenu = document.querySelector(this.asideMenuSelector) as HTMLElement;
      this.asideContent = document.querySelector(this.asideContentSelector) as HTMLElement;
      this.asideGrid = document.querySelector(this.asideGridSelector) as HTMLElement;
      this.asideBackdrop = document.querySelector(this.asideBackdropSelector) as HTMLElement;
      this.closeBtn = document.querySelector(this.closeBtnSelector) as HTMLElement;
    }

    // Fecha ao navegar
    const sb = this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd || event instanceof NavigationCancel) {
        this.hide();
      }
    });
    this.subscriptions.push(sb);

    // Usa renderer.listen em vez de addEventListener
    if (this.asideBackdrop) {
      const unlisten = this.renderer.listen(this.asideBackdrop, 'click', () => this.hide());
      this.unlisteners.push(unlisten);
    }
    if (this.closeBtn) {
      const unlistenClose = this.renderer.listen(this.closeBtn, 'click', () => this.hide());
      this.unlisteners.push(unlistenClose);
    }  
    
    if (this.asideContent) {
      // pointer events para drag
      /**
       * 
      const unlistenDown = this.renderer.listen('document', 'pointerdown', this.onDragStart.bind(this));
      const unlistenMove = this.renderer.listen('document', 'pointermove', this.onDragMove.bind(this));
      const unlistenUp = this.renderer.listen('document', 'pointerup', this.onDragEnd.bind(this));
      this.unlisteners.push(unlistenDown, unlistenMove, unlistenUp);
      */
    }    
  }

  @HostListener('click', ['$event'])
  onClick(ev: Event): void {
    ev.preventDefault();
    this.toggle();
  }

  toggle(): void {
    this.clicked ? this.hide() : this.show();
  }

  private show(): void {
    this.clicked = true;
    this.renderer.setAttribute(document.body, 'data-mv-app-aside-minimize', 'on');
    this.animateOpen();
  }

  private hide(): void {
    this.clicked = false;
    setTimeout(() => {
      this.renderer.removeAttribute(document.body, 'data-mv-app-aside-minimize');
    }, 500);
    this.animateClose();
  }

  /** Drag Event */
  private onDragStart(event: PointerEvent) {
    this.isDragging = true;
    this.startX = event.clientX;
    gsap.set(this.asideContent, { clearProps: 'transition' });
  }

  private onDragMove(event: PointerEvent) {
    if (!this.isDragging || !this.asideContent || !this.asideBackdrop) return;
    this.currentX = event.clientX;
    const deltaX = Math.min(Math.max(this.currentX - this.startX, -this.asideContent.clientWidth), 0);
    this.asideContent.style.transform = `translateX(${deltaX}px)`;
    this.asideBackdrop.style.opacity = `${1 + deltaX / this.asideContent.clientWidth}`;
  }

  private onDragEnd(event: PointerEvent) {
    if (!this.isDragging) return;
    this.isDragging = false;
    const deltaX = this.currentX - this.startX;
    if (deltaX > -300) {
      this.hide(); // arrastou para esquerda → fecha
    } else {
      this.show(); // arrastou pouco → volta
    }
  }

  /** Animação de abrir */
  private animateOpen(): void {
    if (!this.asideBackdrop || !this.asideContent || !this.asideGrid) return;

    gsap.timeline()
      .set([this.asideBackdrop, this.asideContent, this.asideGrid], {
        willChange: 'transform,opacity,background-color',
      })
      .fromTo(this.asideBackdrop, { opacity: 0 }, { opacity: 1, duration: 0.3 }, 0)
      .fromTo(this.asideContent, { x: '100%' }, { x: '0%', ease: 'expo.out', duration: 1 }, 0)
      .fromTo(this.asideGrid, { x: '-35%' }, { x: '0%', ease: 'expo.out', duration: 1 }, 0)
      .fromTo(this.asideGrid, { opacity: 0 }, { opacity: 1, duration: 0.3 }, 0.1)
      .set([this.asideBackdrop, this.asideContent, this.asideGrid], { willChange: 'auto' });
  }

  /** Animação de fechar */
  private animateClose(): void {
    if (!this.asideBackdrop || !this.asideContent || !this.asideGrid) return;

    gsap.timeline()
      .fromTo(this.asideBackdrop, { opacity: 1 }, { opacity: 0, duration: 0.6 }, 0)
      .fromTo(this.asideGrid, { opacity: 1 }, { opacity: 0, duration: 0.2 }, 0)
      .fromTo(this.asideContent, { x: '0%' }, { x: '100%', duration: 0.6 }, 0)
      .set([this.asideBackdrop, this.asideContent, this.asideGrid], { willChange: 'auto' });
  }

  ngOnDestroy(): void {
    this.subscriptions.forEach((sb) => sb.unsubscribe());
    this.unlisteners.forEach((fn) => fn()); // remove os listeners do DOM
  }
}
