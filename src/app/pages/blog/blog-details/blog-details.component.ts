import { ChangeDetectorRef, Component, Inject, OnDestroy, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { catchError, of, Subscription, switchMap } from 'rxjs';
import { DOCUMENT } from '@angular/common';

import { SwiperOptions } from 'swiper/types';

import { 
  PostService, 
  Posts, 
  // Seo
  SeoService,
  JsonLdService,
  CategoryService
} from '../../../core';

@Component({
  selector: 'app-blog-details',
  templateUrl: './blog-details.component.html'
})
export class BlogDetailsComponent implements OnInit, OnDestroy {
  post: Posts | undefined | null;

  slug: string | undefined | null;

  private subscriptions: Subscription[] = [];

  constructor(
    private cdr: ChangeDetectorRef,
    private route: ActivatedRoute,
    private router: Router,
    // seo
    private readonly seo: SeoService,
    private readonly jsonLdService: JsonLdService,
    @Inject(DOCUMENT) private document: any,      
    // Services
    public posts: PostService,    
    public categories: CategoryService,    
  ) {}

  ngOnInit(): void { 
    const sb = this.route.paramMap.pipe(
      switchMap(params => {
        // get slug from URL
        this.slug = String(params.get('slug'));
        if (this.slug || this.slug !== '') {
          return this.posts.getItemById(this.slug);
        }
        return of(undefined);
      }),
      catchError(errorMessage => {
        console.log(errorMessage);
        return of(undefined);
      }),
    ).subscribe((res) => {
      if (!res) {
        this.router.navigate(['/blog'], { relativeTo: this.route });
      }
      this.post = res as Posts;
      this.cdr.detectChanges();
      this.loadSeo();
      this.loadCategory();
    });
    this.subscriptions.push(sb);       
  }    

  loadCategory() {
    const sb = this.categories.getPostCategoriesById(this.post?.id).pipe(
    ).subscribe();
    this.subscriptions.push(sb);      
  }  
  
  loadSeo() {
    // SEO
    this.seo.setData({
      title: this.post?.name,
      description: this.post?.description,
      image: this.post?.pictures.length > 0 ? this.post?.pictures[0].thumb : ''
    });
    const jsonLd = this.jsonLdService.getObject('Website', {
      name: this.post?.name,
      url: this.document.location.origin + this.document.location.pathname
    });        
    this.jsonLdService.setData(jsonLd);    
  }     
    
  ngOnDestroy() {
    this.subscriptions.forEach(sb => sb.unsubscribe());
  }    

   // Swiper
   config: SwiperOptions = {
    slidesPerView: 1,
    spaceBetween: 0,
    grabCursor: false,
    navigation: false,
    pagination: {
      enabled: true,
      type: 'bullets',
      el: '.blog-details-pagination'
    },  
  }   
}
