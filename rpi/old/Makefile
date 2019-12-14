CC=gcc
CFLAGS=-I. -Wall
DEPS = 
OBJ = HTU21D.o HTU21D_test.o
EXTRA_LIBS=-lwiringPi -lcurl -lm -ldl -ljwt

%.o: %.c $(DEPS)
	$(CC) -c -o $@ $< $(CFLAGS)

HTU21D_test: $(OBJ)
	$(CC) -o $@ $^ $(CFLAGS) $(EXTRA_LIBS)

.PHONY: clean

clean:
	rm -f HTU21D $(OBJ) 
