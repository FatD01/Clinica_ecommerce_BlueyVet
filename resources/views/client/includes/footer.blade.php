<footer class="bg-bluey-secondary-light2 text-bluey-light py-5">
    <div class="container mx-auto px-4">
        {{-- Primera fila de contenido principal del footer --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
            <div class="space-y-4">
                <a href="{{ url('/') }}" class="inline-block">
                    <span class="text-3xl font-bold text-bluey-primary hover:text-bluey-secondary-light transition duration-300 ">
                        BlueyVet
                    </span>
                </a>
                <p class="text-bluey-dark">Cuidado excepcional para tus amigos peludos.</p>
                <div class="flex space-x-4 pt-2">
                    <a href="#" class="bg-bluey-dark hover:bg-bluey-primary text-bluey-primary hover:text-bluey-dark w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 border border-bluey-primary">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="bg-bluey-dark hover:bg-bluey-primary text-bluey-primary hover:text-bluey-dark w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 border border-bluey-primary">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="bg-bluey-dark hover:bg-bluey-primary text-bluey-primary hover:text-bluey-dark w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 border border-bluey-primary">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>

            <div class="px-4">
                <h3 class="text-lg font-semibold text-bluey-primary mb-4 pb-2 border-b border-bluey-primary/30">Enlaces Rápidos</h3>
                <ul class="space-y-3">
                    <li><a href="{{ route('client.home') }}" class="text-bluey-dark hover:text-bluey-primary transition duration-300 flex items-center">
                            <i class="fas fa-chevron-right text-xs text-bluey-primary hover:text-bluey-dark mr-2"></i> Inicio</a></li>
                    <li><a href="{{ route('client.citas.create') }}" class=" text-bluey-dark hover:text-bluey-primary transition duration-300 flex items-center">
                            <i class="fas fa-chevron-right text-xs text-bluey-primary mr-2"></i> Agendar Cita</a></li>
                    <li><a href="{{ route('client.citas.index') }}" class="text-bluey-dark hover:text-bluey-primary transition duration-300 flex items-center">
                            <i class="fas fa-chevron-right text-xs text-bluey-primary mr-2"></i> Mis Citas</a></li>
                    <li><a href="{{route('client.servicios.index')}}" class=" text-bluey-dark hover:text-bluey-primary transition duration-300 flex items-center">
                            <i class="fas fa-chevron-right text-xs text-bluey-primary mr-2"></i> Servicios</a></li>
                    <li><a href="{{ route('about.us') }}" class=" text-bluey-dark hover:text-bluey-primary transition duration-300 flex items-center">
                            <i class="fas fa-chevron-right text-xs text-bluey-primary mr-2"></i> Nosotros</a></li>
                    <li><a href="{{ route('faqs.index') }}" class=" text-bluey-dark hover:text-bluey-primary transition duration-300 flex items-center">
                            <i class="fas fa-chevron-right text-xs text-bluey-primary mr-2"></i> FAQ</a></li>
    
                </ul>

            </div>


            <div class="col-span-1 md:col-span-2 lg:col-span-1 space-y-8 px-4"> {{-- Puede ocupar más columnas en md para mejor layout --}}
                <div>
                    <h3 class="text-lg font-semibold text-bluey-primary mb-4 pb-2 border-b border-bluey-primary/30">Horario</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="far fa-clock text-bluey-secondary mt-1 mr-2"></i>
                            <div>
                                <p class="font-medium text-bluey-dark">Lunes - Domingo</p>
                                <p class="text-sm text-bluey-dark">Las 24Hrs</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-bluey-primary mb-4 pb-2 border-b border-bluey-primary/30">Contacto</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt text-bluey-secondary mt-1 mr-2"></i>
                            <span class="text-bluey-dark">Av. Siempre Viva 742, Lima, Perú</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-phone text-bluey-secondary mt-1 mr-2"></i>
                            <span class="text-bluey-dark">+51 987 654 321</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-envelope text-bluey-secondary mt-1 mr-2"></i>
                            <span class="text-bluey-dark">info@blueyvet.com</span>
                        </li>
                    </ul>
                </div>
            </div> {{-- Fin de la columna de Horario y Contacto --}}
            {{-- NUEVA COLUMNA PARA LA IMAGEN --}}
            <div class="flex items-center justify-center p-4"> {{-- Añade padding y centra el contenido --}}
                <img src="{{ asset('img/footer_logo.png') }}" alt="Logo de BlueyVet" class="w-full h-auto max-w-xs mx-auto rounded-lg  object-cover">
            </div>

        </div> {{-- Fin del grid principal --}}

        {{-- Sección de Derechos de Autor y Políticas --}}
        <div class="border-t border-bluey-primary/20 pt-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-sm text-bluey-dark/80 mb-2 md:mb-0">
                    &copy; {{ date('Y') }} BlueyVet. Todos los derechos reservados.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-sm text-bluey-dark hover:text-bluey-primary transition duration-300">Política de Privacidad</a>
                    <a href="#" class="text-sm text-bluey-dark hover:text-bluey-primary transition duration-300"> Términos de Servicio</a>
                </div>
            </div>
        </div>
    </div>
</footer>